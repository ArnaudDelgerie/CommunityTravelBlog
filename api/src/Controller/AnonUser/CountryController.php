<?php

namespace App\Controller\AnonUser;

use App\Repository\CountryRepository;
use App\Serializer\Schema\CountrySchema;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/country")
 */
class CountryController extends AbstractController
{
    private $countryRepository;
    private $schema;

    public function __construct(CountryRepository $countryRepository)
    {
        $this->countryRepository = $countryRepository;
        $this->schema = new CountrySchema();
    }

    /**
     * @Route("", methods={"GET"})
     */
    public function fetchCountries()
    {
        $countries = $this->countryRepository->findBy([], ["name" => "ASC"]);
        $countries = $this->get('serializer')->normalize($countries, 'json', $this->schema->fetchCountries());

        return new JsonResponse([
            "success" => true,
            "data" => $countries
        ], 200);
    }

    /**
     * @Route("/name/{name}", methods={"GET"})
     */
    public function fetchCountriesByName($name)
    {
        if (strlen($name) < 2) {
            return new JsonResponse([
                "success" => false,
                "message" => "The length of the name must be greater than 2"
            ], 400);
        }

        $countries = $this->countryRepository->findByName($name);
        $countries = $this->get('serializer')->normalize($countries, 'json', $this->schema->fetchCountries());

        return new JsonResponse([
            "success" => true,
            "data" => $countries
        ], 200);
    }
}
