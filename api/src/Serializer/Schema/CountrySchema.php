<?php

namespace App\Serializer\Schema;

class CountrySchema
{
    public function fetchCountries()
    {
        return [
            "attributes" => [
                "id",
                "name"
            ]
        ];
    }
}