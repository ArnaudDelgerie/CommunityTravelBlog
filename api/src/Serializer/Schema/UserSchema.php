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

    public function fetchUsers()
    {
        return [
            "attributes" => [
                "firstName",
                "lastName",
                "email",
                "createdAt",
                "active"
            ]
        ];
    }

    public function fetchUser()
    {
        return [
            "attributes" => [
                "firstName",
                "lastName",
                "email",
                "createdAt",
                "active"
            ]
        ];
    }
}