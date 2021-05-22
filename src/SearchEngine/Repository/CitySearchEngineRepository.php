<?php


namespace App\SearchEngine\Repository;


use App\User\Model\Entity\City;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;
use Symfony\Component\Serializer\SerializerInterface;

class CitySearchEngineRepository extends SearchEngineRepository
{
    private SerializerInterface $serializer;

    public function __construct(ContainerBagInterface $params, SerializerInterface $serializer)
    {
        parent::__construct($params, 'city');
        $this->serializer = $serializer;
    }

    public function create(City $city)
    {
        $this->createDocument($city->getId(), $this->serializer->serialize($city, 'json',[
            AbstractNormalizer::GROUPS => ['es_city']
        ]));
    }
}