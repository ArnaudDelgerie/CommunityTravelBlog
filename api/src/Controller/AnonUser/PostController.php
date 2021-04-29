<?php

namespace App\Controller\AnonUser;

use App\Repository\PostRepository;
use App\Repository\CountryRepository;
use App\Serializer\Schema\PostSchema;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/post")
 */
class PostController extends AbstractController
{
    private $postRepository;
    private $countryRepository;
    private $schema;
    private $nbResult = 3;

    public function __construct(
        PostRepository $postRepository,
        CountryRepository $countryRepository,
        PostSchema $schema
    ) {
        $this->postRepository = $postRepository;
        $this->countryRepository = $countryRepository;
        $this->schema = $schema;
    }

    /**
     * @Route("/last", methods={"GET"})
     */
    public function fecthLastPosts()
    {
        //fetch 5 last posts
        $posts = $this->postRepository->findBy(["active" => true, "validated" => true], ["createdAt" => "DESC"], 3, null);

        //normalize posts
        $posts = $this->get("serializer")->normalize($posts, 'json', $this->schema->fetchUserPosts());

        return new JsonResponse([
            "success" => true,
            "data" => $posts
        ], 200);
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
        $posts = $this->postRepository->findBy(["active" => true, "validated" => true], ["createdAt" => "DESC"], $this->nbResult, $offset);

        //calculates total number of pages
        $nbPages = ceil($this->postRepository->countActivePosts() / $this->nbResult);

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
        $posts = $this->postRepository->findBy(["active" => true, "validated" => true, "relatedCountry" => $id], ["createdAt" => "DESC"], $this->nbResult, $offset);

        //calculates total number of pages
        $nbPages = ceil($this->postRepository->countActivePostsByCountry($country->getId()) / $this->nbResult);

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
     * @Route("/{id}", methods={"GET"})
     */
    public function fetchPost($id)
    {
        //fetch and check if post exist
        $post = $this->postRepository->findOneBy(["id" => $id, "active" => true, "validated" => true]);
        if (!$post) {
            return new JsonResponse(["success" => false, "message" => "post not found"]);
        }

        //normalize posts
        $post = $this->get("serializer")->normalize($post, 'json', $this->schema->fetchPost());

        return new JsonResponse([
            "success" => true,
            "data" => $post
        ], 200);
    }
}
