<?php

namespace App\Controller\AuthUser;

use App\Entity\Post;
use App\Entity\PostImage;
use App\Service\Base64Service;
use App\Repository\PostRepository;
use App\Repository\CountryRepository;
use App\Repository\PostImageRepository;
use App\Serializer\Schema\PostSchema;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Validator\ValidatorInterface as Validator;

/**
 * @Route("/api/post")
 */
class PostController extends AbstractController
{
    private $manager;
    private $validator;
    private $countryRepository;
    private $postRepository;
    private $postImageRepository;
    private $base64Service;
    private $schema;

    public function __construct(
        EntityManager $manager,
        Validator $validator,
        PostRepository $postRepository,
        PostImageRepository $postImageRepository,
        CountryRepository $countryRepository,
        PostSchema $schema
    ) {
        $this->manager = $manager;
        $this->validator = $validator;
        $this->postRepository = $postRepository;
        $this->postImageRepository = $postImageRepository;
        $this->countryRepository = $countryRepository;
        $this->base64Service = new Base64Service();
        $this->schema = $schema;
    }

    /**
     * @Route("/user", methods={"GET"})
     */
    public function fetchUserPosts()
    {
        //get logged user
        $loggedUser = $this->getUser();

        //fetch user posts
        $posts = $this->postRepository->findBy(["createdBy" => $loggedUser->getId()]);

        //normailze user posts
        $posts = $this->get("serializer")->normalize($posts, 'json', $this->schema->fetchUserPosts());

        return new JsonResponse([
            "success" => true,
            "data" => $posts
        ], 200);
    }

    /**
     * @Route("", methods={"POST"})
     */
    public function createPost(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        //check if country exist
        $country = $this->countryRepository->find($data["country"] ?? 0);
        if (!$country) {
            return new JsonResponse(["success" => false, "message" => "country not found"], 401);
        }

        //get logged user
        $loggedUser = $this->getUser();

        //create Post and set attributes
        $post = new Post();
        $post->setTitle($data["title"] ?? null);
        $post->setContent($data["content"] ?? null);
        $post->setRelatedCountry($country);
        $post->setCreatedBy($loggedUser);
        $post->setCreatedAt();
        $post->setActive(true);
        $post->setValidated(true);

        //check data error
        foreach ($this->validator->validate($post) as $violation) {
            if ($violation->getMessage()) {
                return new JsonResponse(["success" => false, "message" => $violation->getMessage()], 401);
            }
        }

        //check and decode base64, create PostImage and set attributes
        $error = $this->decodeAndAddImage($data, $post);
        if ($error) {
            return new JsonResponse(["success" => false, "message" => $error], 401);
        }

        //save in db
        $this->manager->persist($post);
        $this->manager->flush();

        return new JsonResponse(["success" => true], 201);
    }

    /**
     * @Route("/{id}", methods={"PATCH"})
     */
    public function updatePost($id, Request $request)
    {
        $data = json_decode($request->getContent(), true);

        //get logged user
        $loggedUser = $this->getUser();

        //check if post exist
        $post = $this->postRepository->findOneBy(["id" => $id, "createdBy" => $loggedUser->getId()]);
        if (!$post) {
            return new JsonResponse(["success" => false, "message" => "post not found"], 401);
        }

        //check if country exist
        $country = $this->countryRepository->find($data["country"] ?? 0);
        if (!$country) {
            return new JsonResponse(["success" => false, "message" => "country not found"], 401);
        }

        //update post
        $post->setTitle($data["title"] ?? null);
        $post->setContent($data["content"] ?? null);
        $post->setRelatedCountry($country);

        //check data error
        foreach ($this->validator->validate($post) as $violation) {
            if ($violation->getMessage()) {
                return new JsonResponse(["success" => false, "message" => $violation->getMessage()], 401);
            }
        }

        //save in db
        $this->manager->persist($post);
        $this->manager->flush();

        return new JsonResponse(["success" => true], 201);
    }

    /**
     * @Route("/{id}/add/image", methods={"POST"})
     */
    public function addImageToPost($id, Request $request)
    {
        $data = json_decode($request->getContent(), true);

        //get logged user
        $loggedUser = $this->getUser();

        //check if post exist
        $post = $this->postRepository->findOneBy(["id" => $id, "createdBy" => $loggedUser->getId()]);
        if (!$post) {
            return new JsonResponse(["success" => false, "message" => "post not found"], 401);
        }

        //check nb total of imagePost associated to Post
        $nbImagePosts = $this->postImageRepository->countPostImages($post->getId());
        if ($nbImagePosts >= 5) {
            return new JsonResponse(["success" => false, "message" => "post have already 5 images"], 401);
        }

        //check and decode base64, create PostImage and set attributes
        $images = array();
        $images['images'][] = $data;
        $error = $this->decodeAndAddImage($images, $post);
        if ($error) {
            return new JsonResponse(["success" => false, "message" => $error], 401);
        }

        //save in db
        $this->manager->persist($post);
        $this->manager->flush();

        return new JsonResponse(["success" => true], 201);
    }

    /**
     * @Route("/{id}", methods={"DELETE"})
     */
    public function deletePost($id)
    {
        //get logged user
        $loggedUser = $this->getUser();

        //check if post exist
        $post = $this->postRepository->findOneBy(["id" => $id, "createdBy" => $loggedUser->getId()]);
        if (!$post) {
            return new JsonResponse(["success" => false, "message" => "post not found"], 401);
        }

        //delete postImages to server
        $images = $post->getPostImages();
        foreach ($images as $image) {
            $filesystem = new Filesystem();
            $filesystem->remove($this->getParameter("post_img_dir") . "/" . $image->getImage());
        }

        //remove post and postImage to db
        $this->manager->remove($post);
        $this->manager->flush();

        return new JsonResponse(["success" => true], 204);
    }

    private function decodeAndAddImage($data, &$post)
    {
        if (!isset($data["images"]) || !is_array($data["images"]) || count($data["images"]) < 1 || count($data["images"]) > 5) {
            return "you must add between 1 and 5 images";
        }
        $i = 0;
        foreach ($data["images"] as $image) {
            if (!isset($image["description"]) || !is_string($image["description"]) || strlen($image["description"]) < 1 || strlen($image["description"]) > 140) {
                return "images[" . $i . "].description required (max length: 140)";
            } elseif (!$this->base64Service->checkData($image["image"] ?? null)) {
                return "images[" . $i . "].image is not png, jpg or jpeg Base64";
            } else {
                $fileName = $this->base64Service->convertToFile($this->getParameter("post_img_dir"));
                $postImage = new PostImage();
                $postImage->setDescription($image['description']);
                $postImage->setImage($fileName);
                $this->manager->persist($postImage);
                $post->addPostImage($postImage);
                $i++;
            }
        }
    }
}
