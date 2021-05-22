<?php

namespace App\SearchEngine\Repository;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

abstract class SearchEngineRepository implements SearchEngineRepositoryInterface
{
    private Client $client;
    private string $indexName;

    public function __construct(ContainerBagInterface $params, string $indexName)
    {
        $this->indexName = $indexName;

        $this->client = ClientBuilder::create()
            ->setHosts([
                $params->get('es_host')
            ])->build();
    }

    /*
     *  Search methods (read)
     */

    /**
     * @param array $query
     * @return string|null
     */
    protected function search(array $query): ?string
    {
        $query = array_merge([
            'index' => $this->indexName
        ], $query);

        $response = $this->client->get($query);

        if (null === $response || !isset($response['_source'])) {
            return null;
        }

        return json_encode($response['_source']);
    }

    /**
     * Find document by id
     *
     * @param mixed $id
     * @return array|callable
     */
    public function findById($id)
    {
        $query = [
            'index' => $this->indexName,
            'id' => $id
        ];

        return $this->client->search($query);
    }

    /**
     * Find documents by id
     *
     * @throws Exception
     */
    public function findByIds()
    {
        throw new Exception('Method not implemented');
    }

    /*
     * Edit methods (create/update/delete)
     */

    /**
     * Create a document to search engine
     *
     * @param mixed $id
     * @param string $document
     */
    protected function createDocument($id, string $document)
    {
        $body = json_decode($document, true);

        $this->client->index([
            'index' => $this->indexName,
            'id' => $id,
            'body' => $body
        ]);
    }
}