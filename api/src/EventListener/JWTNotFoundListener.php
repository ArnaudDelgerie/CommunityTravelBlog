<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent;

class JWTNotFoundListener
{
  /**
 * @param JWTNotFoundEvent $event
 */
public function onJWTNotFound(JWTNotFoundEvent $event)
  {
    $data = [
      'success'  => false,
      'message' => 'Missing token',
    ];

    $response = new JsonResponse($data, 401);

    $event->setResponse($response);
  }
}