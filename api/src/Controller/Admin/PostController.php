<?php

namespace App\Controller\Admin;

use App\Repository\PostRepository;
use App\Repository\CountryRepository;
use App\Serializer\Schema\PostSchema;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/api/admin/post")
 */
class PostController extends AbstractController
{
    private $manager;
    private $postRepository;
    private $countryRepository;
    private $schema;
    private $nbResult = 3;

    public function __construct(
        EntityManager $manager,
        PostRepository $postRepository,
        CountryRepository $countryRepository,
        PostSchema $schema
    ) {
        $this->manager = $manager;
        $this->postRepository = $postRepository;
        $this->countryRepository = $countryRepository;
        $this->schema = $schema;
    }

    /**
     * @Route("", methods={"GET"})
     */
    public function fetchPosts(Request $request)
    {
        //get or set page
        $page = $request->query->get("page");
        if ($page === null || $page <= 0) {
            $page = 1;
        }
        $offset = ($page - 1) * $this->nbResult;

        //fetch posts
        $posts = $this->postRepository->findBy([], ["createdAt" => "DESC"], $this->nbResult, $offset);

        //calculates total number of pages
        $nbPages = ceil($this->postRepository->countPosts() / $this->nbResult);

        //normalize posts
        $posts = $this->get("serializer")->normalize($posts, 'json', $this->schema->fetchUserPosts());

        return new JsonResponse([
            "success" => true,
            "data" => [
                "posts" => $posts,
                "nb_pages" => $nbPages
            ]
        ], 200);
    }

    /**
     * @Route("/country/{id}", methods={"GET"})
     */
    public function fetchPostsByCountry($id, Request $request)
    {
        //check if country exist
        $country = $this->countryRepository->find($id);
        if (!$country) {
            return new JsonResponse(["success" => false, "message" => "country not found"]);
        }

        //get or set page
        $page = $request->query->get("page");
        if ($page === null || $page <= 0) {
            $page = 1;
        }
        $offset = ($page - 1) * $this->nbResult;

        //fetch posts
        $posts = $this->postRepository->findBy(["relatedCountry" => $id], ["createdAt" => "DESC"], $this->nbResult, $offset);

        //calculates total number of pages
        $nbPages = ceil($this->postRepository->countPostsByCountry($country->getId()) / $this->nbResult);

        //normalize posts
        $posts = $this->get("serializer")->normalize($posts, 'json', $this->schema->fetchUserPosts());

        return new JsonResponse([
            "success" => true,
            "data" => [
                "posts" => $posts,
                "nb_pages" => $nbPages
            ]
        ], 200);
    }

    /**
     * @Route("/{id}/disable", methods={"PATCH"})
     */
    public function disablePost($id)
    {
        //fetch and check if post exist and validated
        $post = $this->postRepository->find($id);
        if (!$post) {
            return new JsonResponse(["success" => false, "message" => "post not found"]);
        } elseif (!$post->getValidated()) {
            return new JsonResponse(["success" => false, "message" => "post is already disabled"]);
        }

        //disable post
        $post->setValidated(false);

        //save in db
        $this->manager->persist($post);
        $this->manager->flush();

        return new JsonResponse(["success" => true], 201);
    }

    /**
     * @Route("/{id}/enable", methods={"PATCH"})
     */
    public function enablePost($id)
    {
        //fetch and check if post exist and validated
        $post = $this->postRepository->find($id);
        if (!$post) {
            return new JsonResponse(["success" => false, "message" => "post not found"]);
        } elseif ($post->getValidated()) {
            return new JsonResponse(["success" => false, "message" => "post is already enabled"]);
        }

        //disable post
        $post->setValidated(true);

        //save in db
        $this->manager->persist($post);
        $this->manager->flush();

        return new JsonResponse(["success" => true], 201);
    }
}
