<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

class JWTInvalidListener
{
  /**
   * @param JWTInvalidEvent $event
   */
  public function onJWTInvalid(JWTInvalidEvent $event)
  {
    $data = [
      'success'  => false,
      'message' => 'Your token is invalid, please login again to get a new one',
    ];

    $response = new JsonResponse($data, 401);

    $event->setResponse($response);
  }
}
