<?php

namespace App\Controller\AnonUser;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface as Validator;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    private $encoder;
    private $manager;
    private $validator;
    private $userRepository;

    public function __construct(
        UserPasswordEncoderInterface $encoder,
        EntityManager $manager,
        Validator $validator,
        UserRepository $userRepository
    ) {
        $this->encoder = $encoder;
        $this->manager = $manager;
        $this->validator = $validator;
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/register", methods={"POST"})
     */
    public function register(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        // check email, return error if already used
        $userExist = $this->userRepository->findOneBy(array("email" => $data['email'] ?? null));
        if ($userExist) {
            return new JsonResponse(["success" => false, "message" => "email already used"], 401);
        }

        //create user and set attributes
        $user = $this->get('serializer')->deserialize($request->getContent(), User::class, 'json');
        $user->setCreatedAt();
        $user->setRoles(['ROLE_USER']);
        $user->setActive(true);

        //check data error
        foreach ($this->validator->validate($user) as $violation) {
            if ($violation->getMessage()) {
                return new JsonResponse(["success" => false, "message" => $violation->getMessage()], 400);
            }
        }

        //hash password
        $user->setPassword($this->encoder->encodePassword($user, $data['password']));
        
        //save in db
        $this->manager->persist($user);
        $this->manager->flush();

        return new JsonResponse(["success" => true], 201);
    }
}
