<?php

namespace App\Controller\Admin;

use App\Repository\UserRepository;
use App\Serializer\Schema\UserSchema;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/api/admin/user")
 */
class UserController extends AbstractController
{
    private $manager;
    private $schema;
    private $userRepository;
    private $nbResult = 3;

    public function __construct(
        EntityManager $manager,
        UserRepository $userRepository
    ) {
        $this->manager = $manager;
        $this->schema = new UserSchema();
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("", methods={"GET"})
     */
    public function fetchUsers(Request $request)
    {
        //get or set page
        $page = $request->query->get("page");
        if ($page === null || $page <= 0) {
            $page = 1;
        }
        $offset = ($page - 1) * $this->nbResult;

        //fetch users
        $users = $this->userRepository->findBy([], null, $this->nbResult, $offset);

        //calculates total number of pages
        $nbPages = ceil($this->userRepository->countUsers() / $this->nbResult);

        //normalize user
        $users = $this->get('serializer')->normalize($users, 'json', $this->schema->fetchUsers());

        return new JsonResponse([
            "success" => true,
            "data" => [
                "users" => $users,
                "nb_pages" => $nbPages
            ]
        ], 200);
    }

    /**
     * @Route("/enable", methods={"GET"})
     */
    public function fetchEnableUsers(Request $request)
    {
        //get or set page
        $page = $request->query->get("page");
        if ($page === null || $page <= 0) {
            $page = 1;
        }
        $offset = ($page - 1) * $this->nbResult;

        //fetch users
        $users = $this->userRepository->findBy(["active" => true], null, $this->nbResult, $offset);

        //calculates total number of pages
        $nbPages = ceil($this->userRepository->countUsersByStatus(true) / $this->nbResult);

        //normalize user
        $users = $this->get('serializer')->normalize($users, 'json', $this->schema->fetchUsers());

        return new JsonResponse([
            "success" => true,
            "data" => [
                "users" => $users,
                "nb_pages" => $nbPages
            ]
        ], 200);
    }

    /**
     * @Route("/disable", methods={"GET"})
     */
    public function fetchDisableUsers(Request $request)
    {
        //get or set page
        $page = $request->query->get("page");
        if ($page === null || $page <= 0) {
            $page = 1;
        }
        $offset = ($page - 1) * $this->nbResult;

        //fetch users
        $users = $this->userRepository->findBy(["active" => false], null, $this->nbResult, $offset);

        //calculates total number of pages
        $nbPages = ceil($this->userRepository->countUsersByStatus(false) / $this->nbResult);

        //normalize user
        $users = $this->get('serializer')->normalize($users, 'json', $this->schema->fetchUsers());

        return new JsonResponse([
            "success" => true,
            "data" => [
                "users" => $users,
                "nb_pages" => $nbPages
            ]
        ], 200);
    }

    /**
     * @Route("/{id}", methods={"GET"})
     */
    public function fetchUser($id)
    {
        //fetch user
        $user = $this->userRepository->find($id);

        //check if user exist
        if (!$user) {
            return new JsonResponse(["success" => false, "message" => "User not found"], 400);
        }

        //serialize user
        $user = $this->get('serializer')->normalize($user, 'json', $this->schema->fetchUser());

        return new JsonResponse([
            "success" => true,
            "data" => $user
        ], 200);
    }

    /**
     * @Route("/{id}/disable", methods={"PATCH"})
     */
    public function disableUser($id)
    {
        //fetch user
        $user = $this->userRepository->find($id);

        //check if user exist and enable
        if (!$user) {
            return new JsonResponse(["success" => false, "message" => "User not found"], 400);
        } elseif (!$user->getActive()) {
            return new JsonResponse(["success" => false, "message" => "User already disable"], 400);
        }

        //disable user
        $user->setActive(false);

        //save in db
        $this->manager->persist($user);
        $this->manager->flush();

        return new JsonResponse(["success" => true], 201);
    }

    /**
     * @Route("/{id}/enable", methods={"PATCH"})
     */
    public function enableUser($id)
    {
        //fetch user
        $user = $this->userRepository->find($id);

        //check if user exist and enable
        if (!$user) {
            return new JsonResponse(["success" => false, "message" => "User not found"], 400);
        } elseif ($user->getActive()) {
            return new JsonResponse(["success" => false, "message" => "User already enable"], 400);
        }

        //enable user
        $user->setActive(true);

        //save in db
        $this->manager->persist($user);
        $this->manager->flush();

        return new JsonResponse(["success" => true], 201);
    }
}
