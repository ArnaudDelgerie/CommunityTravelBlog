<?php

namespace App\Controller\AuthUser;

use App\Service\Base64Service;
use App\Repository\PostImageRepository;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


/**
 * @Route("/api/post/image")
 */
class ImagePostController extends AbstractController
{
    private $manager;
    private $postImageRepository;

    public function __construct(
        EntityManager $manager,
        PostImageRepository $postImageRepository
    ) {
        $this->manager = $manager;
        $this->postImageRepository = $postImageRepository;
    }

    /**
     * @Route("/{id}/description", methods={"PATCH"})
     */
    public function updatePostImageDescription($id, Request $request)
    {
        $data = json_decode($request->getContent(), true);

        //get logged user
        $loggedUser = $this->getUser();

        //fetch and check if postImage exist
        $image = $this->postImageRepository->find($id);
        if (!$image) {
            return new JsonResponse(["success" => false, "message" => "image not found"], 401);
        }

        //verification of image owner
        if ($image->getPost()->getCreatedBy()->getId() !== $loggedUser->getId()) {
            return new JsonResponse(["success" => false, "message" => "image not found"], 401);
        }

        //check data error
        if (!isset($data["description"]) || !is_string($data["description"]) || strlen($data["description"]) < 1 || strlen($data["description"]) > 140) {
            return  new JsonResponse(["success" => false, "message" => "images description required (max length: 140)"], 401);
        }

        //update postImage description
        $image->setDescription($data['description']);

        //save in db
        $this->manager->persist($image);
        $this->manager->flush();

        return new JsonResponse(["success" => true], 201);
    }

    /**
     *@route("/{id}", methods={"DELETE"})
     */
    public function deletePostImage($id)
    {
        //get logged user
        $loggedUser = $this->getUser();

        //fetch and check if postImage exist
        $image = $this->postImageRepository->find($id);
        if (!$image) {
            return new JsonResponse(["success" => false, "message" => "image not found"], 401);
        }

        //fetch related post and verif of image owner
        $post = $image->getPost();
        if ($post->getCreatedBy()->getId() !== $loggedUser->getId()) {
            return new JsonResponse(["success" => false, "message" => "image not found"], 401);
        }

        //check nb total of imagePost associated to Post
        $nbImagePosts = $this->postImageRepository->countPostImages($post->getId());
        if ($nbImagePosts <= 1) {
            return new JsonResponse(["success" => false, "message" => "a post must contain at least 1 image, add a new one in order to delete it"], 401);
        }

        //delete postImage to server
        $filesystem = new Filesystem();
        $filesystem->remove($this->getParameter("post_img_dir") . "/" . $image->getImage());

        //delete postImage to db
        $this->manager->remove($image);
        $this->manager->flush();

        return new JsonResponse(["success" => true], 204);
    }
}
