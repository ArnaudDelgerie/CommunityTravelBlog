<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTExpiredEvent;

class JWTExpiredListener
{
  /**
   * @param JWTExpiredEvent $event
   */
  public function onJWTExpired(JWTExpiredEvent $event)
  {
    $data = [
      'success'  => false,
      'message' => 'Your token is expired, please renew it.',
    ];

    $response = new JsonResponse($data, 401);

    $event->setResponse($response);
  }
}
