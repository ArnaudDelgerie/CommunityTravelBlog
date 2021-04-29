<?php

namespace App\Serializer\Schema;

class CommentSchema
{
    public function fetchCommentsbyPost()
    {
        return [
            "attributes" => [
                "id",
                "content",
                "createdAt",
                "createdBy" => [
                    "firstName",
                    "lastName"
                ]
            ]
        ];
    }
}