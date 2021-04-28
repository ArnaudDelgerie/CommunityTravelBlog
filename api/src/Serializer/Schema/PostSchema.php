<?php

namespace App\Serializer\Schema;

class PostSchema
{
    public function fetchUserPosts()
    {
        return [
            "attributes" => [
                "id",
                "title",
                "content",
                "createdAt",
                "validated",
                "active",
                "postImages" => [
                    "id",
                    "description",
                    "image"
                ]
            ]
        ];
    }
}