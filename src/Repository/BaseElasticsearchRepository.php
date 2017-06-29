<?php
declare(strict_types=1);

namespace Pac\ProophPackage\Repository;

use Elasticsearch\Client;
use Oscar\Matter\Repository\RepositoryInterface;
use Pac\ProophPackage\Projection\AbstractElasticsearchModel;

class BaseElasticsearchRepository implements RepositoryInterface
{
    /** @var Client */
    protected $client;
    /** @var AbstractElasticsearchModel */
    protected $readModel;

    public function __construct(AbstractElasticsearchModel $readModel)
    {
        $this->client = $readModel->getClient();
        $this->readModel = $readModel;
    }

    public function all()
    {
        $params = [
            'index' => $this->readModel::INDEX_NAME,
            'body' => [

            ],
        ];

        $results = $this->client->search($params);

        if (0 === $results['hits']['total']) {
            return null;
        }

        $response = [];

        foreach ($results['hits']['hits'] as $hit) {
            $response[] = $hit['_source'];
        }

        return $response;
    }

    public function find($id, $fields = [])
    {
        $params = [
            'index' => $this->readModel::INDEX_NAME,
            'body' => [
                'query' => [
                    'match' => [
                        $this->readModel::ID_NAME => $id,
                    ],
                ],
            ],
        ];

        $results = $this->client->search($params);

        if (0 === $results['hits']['total']) {
            return null;
        }

        return $results['hits']['hits'][0]['_source'];
    }
}
