<?php

namespace App\Core\DataFixtures;

use App\Core\Model\Entity\User;
use App\User\DataFixtures\CityFixtures;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    private UserPasswordEncoderInterface $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Faker\Factory::create();

        for ($i = 0 ; $i < 100 ; $i++) {

            $user = new User();
            $password = $this->encoder->encodePassword($user, 'P@ssw0rd');
            $user->setEmail($faker->email);
            $user->setPassword($password);
            $user->setCity($this->getReference(CityFixtures::CITY_REFERENCE.ceil($i%10)));

            $manager->persist($user);
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CityFixtures::class
        ];
    }
}
