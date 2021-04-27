<?php

namespace App\EventListener;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

class JWTFailureListener
{
    /**
     * @param AuthenticationFailureEvent $event
     */
    public function onJWTfailure(AuthenticationFailureEvent $event)
    {
        $data = [
            'success'  => false,
            'message' => 'Bad credentials, please verify that your email/password are correctly set',
        ];

        $response = new JsonResponse($data, 401);

        $event->setResponse($response);
    }
}
