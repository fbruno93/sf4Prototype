<?php

namespace App\SearchEngine\Command;

use App\SearchEngine\Repository\CitySearchEngineRepository;
use App\User\Model\Entity\City;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SearchEngineCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private CitySearchEngineRepository $citySearchEngineRepository;

    public function __construct(EntityManagerInterface $entityManager, CitySearchEngineRepository $citySearchEngineRepository)
    {
        parent::__construct("app:search-engine:populate");
        $this->entityManager = $entityManager;
        $this->citySearchEngineRepository = $citySearchEngineRepository;
    }

    protected function configure()
    {
        $this
            ->addArgument("index", InputArgument::OPTIONAL, "Index to populate", null);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cityRepository = $this->entityManager->getRepository(City::class);

        foreach ($cityRepository->findAll() as $city) {
            $this->citySearchEngineRepository->createDocument($city);
        }

        return Command::SUCCESS;
    }
}
