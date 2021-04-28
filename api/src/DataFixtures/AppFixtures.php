<?php

namespace App\DataFixtures;

use App\Entity\Country;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AppFixtures extends Fixture
{
    private $container;

    public function __construct(ContainerInterface $container = null) {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $this->loadCountries($manager);
    }

    private function loadCountries($manager)
    {
        $countries = json_decode(file_get_contents($this->container->getParameter("json_dir") . "/CountriesFr.json"), true);

        foreach ($countries as $data) {
            $country = new Country();
            $country->setName($data["name"]);
            $manager->persist($country);
        }

        $manager->flush();
    }
}
