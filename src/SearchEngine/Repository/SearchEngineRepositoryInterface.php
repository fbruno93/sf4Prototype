<?php


namespace App\SearchEngine\Repository;


interface SearchEngineRepositoryInterface
{
    public function findById($id);
    public function findByIds();
}