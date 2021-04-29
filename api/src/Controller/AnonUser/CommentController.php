<?php

namespace App\Controller\AnonUser;

use App\Repository\PostRepository;
use App\Repository\CommentRepository;
use App\Serializer\Schema\CommentSchema;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/comment")
 */
class CommentController extends AbstractController
{
    private $postRepository;
    private $commentRepository;
    private $schema;
    private $nbResult = 3;

    public function __construct(
        PostRepository $postRepository,
        CommentRepository $commentRepository,
        CommentSchema $schema
    ) {
        $this->postRepository = $postRepository;
        $this->commentRepository = $commentRepository;
        $this->schema = $schema;
    }

    /**
     * @Route("/post/{id}", methods={"GET"})
     */
    public function FetchCommentsByPost($id, Request $request)
    {
        //fetch and check if post exist
        $post = $this->postRepository->find($id);
        if (!$post) {
            return new JsonResponse(["success" => false, "message" => "post not found"], 401);
        }

        //get or set page
        $page = $request->query->get("page");
        if ($page === null || $page <= 0) {
            $page = 1;
        }
        $offset = ($page - 1) * $this->nbResult;

        //fetch comments
        $comments = $this->commentRepository->findBy(["relatedPost" => $post->getId()], ["createdAt" => "ASC"], $this->nbResult, $offset);

        //calculates total number of pages
        $nbPages = ceil($this->commentRepository->countCommentsByPost($post->getId()) / $this->nbResult);

        //normalize comments
        $comments = $this->get("serializer")->normalize($comments, 'json', $this->schema->FetchCommentsByPost());

        return new JsonResponse([
            "success" => true,
            "data" => [
                "comments" => $comments,
                "nb_pages" => $nbPages
            ]
        ], 200);
    }
}
