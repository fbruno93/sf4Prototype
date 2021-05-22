<?php

namespace App\Core\Model;

use App\Core\Model\Entity\User;
use App\Core\Model\Repository\UserRepository;
use App\SearchEngine\Repository\CitySearchEngineRepository;
use App\User\Model\Entity\City;
use BadMethodCallException;
use Doctrine\ORM\EntityManagerInterface;
use JsonSerializable;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class UserModel implements JsonSerializable, ModelInterface
{
    // Services
    // Data Access Object
    private CitySearchEngineRepository $citySearchEngineRepository;
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;

    // Serializer
    private SerializerInterface $serializer;

    // Attribute JSON
    private ?User $user; // from DB
    private ?City $city; // from ES

    public function __construct(CitySearchEngineRepository $citySearchEngineRepository,
                                UserRepository $userRepository,
                                SerializerInterface $serializer,
                                EntityManagerInterface $entityManager)
    {
        $this->citySearchEngineRepository = $citySearchEngineRepository;
        $this->userRepository = $userRepository;
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
    }

    /**
     * Call this method when trying call an non existent method
     *
     * @param string $name of method tried call
     * @param array $arguments of method tries call
     */
    public function __call(string $name, array $arguments)
    {
        if (!method_exists($this->user, $name)) {
            throw new BadMethodCallException();
        }

        $this->user->$name($arguments);
    }


    public function getCity(): City
    {
        return $this->city;
    }

    public function load(...$parameters)
    {
        $email = $parameters[0];
        $this->user = $this->userRepository->findOneBy(['email' => $email]);
        $city = $this->citySearchEngineRepository->findById($this->user->getCityId());
        $this->city = $this->serializer->deserialize(json_encode($city), City::class, 'json');
    }

    public function jsonSerialize()
    {
        $user = json_decode($this->serializer->serialize($this->user, 'json', [
            AbstractNormalizer::GROUPS => ['user_profile'],
        ]), true);

        $user['city'] = $this->city;

        return $user;
    }

    public function save()
    {
        // /!\ Deprecated method //
        $this->entityManager->merge($this->city);

        $this->entityManager->persist($this->user);
        $this->entityManager->flush();
    }
}
