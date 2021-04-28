<?php

namespace App\Controller\AuthUser;

use App\Repository\UserRepository;
use App\Serializer\Schema\UserSchema;
use Doctrine\DBAL\Connection;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface as Validator;

/**
 * @Route("/api/user")
 */
class UserController extends AbstractController
{
    private $encoder;
    private $manager;
    private $validator;
    private $schema;
    private $userRepository;
    private $dbConnection;

    public function __construct(
        UserPasswordEncoderInterface $encoder,
        EntityManager $manager,
        Validator $validator,
        UserRepository $userRepository,
        Connection $dbConnection
    ) {
        $this->encoder = $encoder;
        $this->manager = $manager;
        $this->validator = $validator;
        $this->schema = new UserSchema();
        $this->userRepository = $userRepository;
        $this->dbConnection = $dbConnection;
    }

    /**
     * @Route("/account", methods={"GET"})
     */
    public function fetchUserAccount()
    {
        //get logged user
        $loggedUser = $this->getUser();

        //normalize user
        $user = $this->get('serializer')->normalize($loggedUser, 'json', $this->schema->fetchUserAccount());

        return new JsonResponse([
            "success" => true,
            "data" => $user
        ], 200);
    }

    /**
     * @Route("/account", methods={"PATCH"})
     */
    public function updateUserAccount(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        //get logged user
        $loggedUser = $this->getUser();

        //set logged user
        $loggedUser->setFirstName($data["first_name"] ?? null);
        $loggedUser->setLastName($data["last_name"] ?? null);

        //check data error
        foreach ($this->validator->validate($loggedUser) as $violation) {
            if ($violation->getMessage()) {
                return new JsonResponse(["success" => false, "message" => $violation->getMessage()], 400);
            }
        }

        //save in db
        $this->manager->persist($loggedUser);
        $this->manager->flush();

        return new JsonResponse(["success" => true], 201);
    }

    /**
     * @Route("/password", methods={"PATCH"})
     */
    public function updateUserPassword(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        //get logged user
        $loggedUser = $this->getUser();

        //check current password validity
        $validPassword = $this->encoder->isPasswordValid($loggedUser, $data["current_password"] ?? "-");
        if (!$validPassword) {
            return new JsonResponse(["success" => false, "message" => "current_password is invalid"], 401);
        }
        
        //set new password
        $loggedUser->setPassword($data["password"] ?? null);

        //check data error
        foreach ($this->validator->validate($loggedUser) as $violation) {
            if ($violation->getMessage()) {
                return new JsonResponse(["success" => false, "message" => $violation->getMessage()], 400);
            }
        }

        //hash password
        $loggedUser->setPassword($this->encoder->encodePassword($loggedUser, $data['password']));
        
        //save in db
        $this->manager->persist($loggedUser);
        $this->manager->flush();

        return new JsonResponse(["success" => true], 201);
    }

    /**
     * @Route("/unregister", methods={"DELETE"})
     */
    public function unregisterUser()
    {
        //get logged user
        $loggedUser = $this->getUser();

        //delete user refresh token
        try {
            $this->dbConnection->executeStatement(sprintf('
            DELETE FROM refresh_tokens
            WHERE username = "%s"
        ', $loggedUser->getUsername()));
        } catch (\Throwable $th) {
            return new JsonResponse(["success" => false, "message" => "current_password is invalid"], 401);
        }

        //user anonymization
        $loggedUser->setFirstname("Utilisateur");
        $loggedUser->setLastName("anonyme");
        $loggedUser->setEmail(bin2hex(random_bytes(10)) . bin2hex(random_bytes(10)));
        $loggedUser->setActive(false);

        //save in db
        $this->manager->persist($loggedUser);
        $this->manager->flush();

        return new JsonResponse(["success" => true], 201);
    }
}
