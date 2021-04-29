<?php

namespace App\Controller\AuthUser;

use App\Entity\Comment;
use App\Repository\PostRepository;
use App\Repository\CommentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Validator\ValidatorInterface as Validator;

/**
 * @Route("/api/comment")
 */
class CommentController extends AbstractController
{
    private $manager;
    private $validator;
    private $postRepository;
    private $commentRepository;

    public function __construct(
        EntityManager $manager,
        Validator $validator,
        PostRepository $postRepository,
        CommentRepository $commentRepository
    ) {
        $this->manager = $manager;
        $this->validator = $validator;
        $this->postRepository = $postRepository;
        $this->commentRepository = $commentRepository;
    }

    /**
     * @Route("/post/{id}", methods={"POST"})
     */
    public function createComment($id, Request $request)
    {
        $data = json_decode($request->getContent(), true);

        //get logged user
        $loggedUser = $this->getUser();

        //fetch and check if post exist
        $post = $this->postRepository->find($id);
        if (!$post) {
            return new JsonResponse(["success" => false, "message" => "post not found"], 401);
        }

        //create comment and set attributes
        $comment = new Comment();
        $comment->setContent($data['content'] ?? null);
        $comment->setRelatedPost($post);
        $comment->setCreatedBy($loggedUser);
        $comment->setCreatedAt();

        //check data error
        foreach ($this->validator->validate($comment) as $violation) {
            if ($violation->getMessage()) {
                return new JsonResponse(["success" => false, "message" => $violation->getMessage()], 401);
            }
        }

        //save in db
        $this->manager->persist($comment);
        $this->manager->flush();

        return new JsonResponse(["success" => true], 201);
    }

    /**
     * @Route("/{id}", methods={"DELETE"})
     */
    public function deleteComment($id)
    {
        //get logged user
        $loggedUser = $this->getUser();

        //fetch and check if comment exist
        $comment = $this->commentRepository->findOneBy(["id" => $id, "createdBy" => $loggedUser->getId()]);
        if (!$comment) {
            return new JsonResponse(["success" => false, "message" => "comment not found"], 401);
        }

        //delete comment to db
        $this->manager->remove($comment);
        $this->manager->flush();

        return new JsonResponse(["success" => true], 204);
    }
}
