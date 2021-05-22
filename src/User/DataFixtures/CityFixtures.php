<?php

namespace App\User\DataFixtures;

use App\User\Model\Entity\City;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;

class CityFixtures extends Fixture
{
    public const CITY_REFERENCE = "city";

    public function load(ObjectManager $manager)
    {
        $faker = Faker\Factory::create();

        for($i = 0 ; $i < 10 ; $i++) {
            $city = new City();
            $city->setName($faker->city);
            $city->setDescription($faker->text);

            $this->addReference(self::CITY_REFERENCE.$i, $city);
            $manager->persist($city);
        }

        $manager->flush();

    }
}