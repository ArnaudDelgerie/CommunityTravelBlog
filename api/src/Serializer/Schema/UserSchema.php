<?php

namespace App\Serializer\Schema;

class UserSchema
{
    public function fetchUserAccount()
    {
        return [
            "attributes" => [
                "firstName",
                "lastName",
                "email",
                "createdAt"
            ]
        ];
    }
}