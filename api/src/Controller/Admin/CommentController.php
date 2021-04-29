<?php

namespace App\Controller\Admin;

use App\Repository\PostRepository;
use App\Repository\CommentRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/api/admin/comment")
 */
class CommentController extends AbstractController
{
    private $manager;
    private $postRepository;
    private $commentRepository;

    public function __construct(
        EntityManager $manager,
        PostRepository $postRepository,
        CommentRepository $commentRepository
    ) {
        $this->manager = $manager;
        $this->postRepository = $postRepository;
        $this->commentRepository = $commentRepository;
    }

    /**
     * @Route("/{id}", methods={"DELETE"})
     */
    public function deleteComment($id)
    {
        //get logged user
        $loggedUser = $this->getUser();

        //fetch and check if comment exist
        $comment = $this->commentRepository->find($id);
        if (!$comment) {
            return new JsonResponse(["success" => false, "message" => "comment not found"], 401);
        }

        //delete comment to db
        $this->manager->remove($comment);
        $this->manager->flush();

        return new JsonResponse(["success" => true], 204);
    }
}
