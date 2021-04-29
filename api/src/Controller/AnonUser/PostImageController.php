<?php

namespace App\Controller\AnonUser;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/post/image")
 */
class PostImageController extends AbstractController
{
    /**
     * @Route("/{fileName}", methods={"GET"})
     */
    public function getPostImage($fileName)
    {
        try {
            $file = new File($this->getParameter("post_img_dir") . "/" . $fileName);
            return $this->file($file);
        } catch (\Throwable $th) {
            return new JsonResponse([
                "success" => false,
                "message" => "Image not found"
            ], 400);
        }
    }
}
