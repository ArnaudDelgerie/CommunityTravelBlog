<?php

namespace App\EventListener;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class JWTEncodedListener
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @param RequestStack $requestStack
     * 
     * @param UserRepository $userRepository
     */
    public function __construct(RequestStack $requestStack, UserRepository $userRepository)
    {
        $this->requestStack = $requestStack;
        $this->userRepository = $userRepository;
    }

    /**
     * @param JWTDecodedEvent $event
     *
     * @return void
     */
    public function onJWTCreated(JWTCreatedEvent $event)
    {
        $payload = $event->getData();
        $user = $this->userRepository->findOneBy(array("email" => $payload['username'], "active" => true));

        if (!$user) {
            throw new CustomUserMessageAuthenticationException();
        }

        $payload['last_name'] = $user->getLastname();
        $payload['first_name'] = $user->getFirstname();

        $event->setData($payload);
    }
}
