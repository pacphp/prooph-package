<?php
declare(strict_types=1);

namespace Pac\ProophPackage\Projection;

use Elasticsearch\Client;
use Prooph\EventStore\Projection\AbstractReadModel;

abstract class AbstractElasticsearchModel extends AbstractReadModel
{
    const ID_NAME = 'id';
    const INDEX_TYPE = null;

    /** @var Client */
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function isInitialized(): bool
    {
        return $this->client->indices()->exists(['index' => static::INDEX_NAME]);
    }

    public function reset(): void
    {
        $params = [
            'index' => static::INDEX_NAME,
            'type'  => static::INDEX_TYPE ?? static::INDEX_NAME,
            'body'  => [
                'query' => [
                    'match_all' => new \stdClass(),
                ],
            ],
        ];
        $response = $this->client->deleteByQuery($params);
    }

    public function delete(): void
    {
         $response = $this->client->indices()->delete(['index' => static::INDEX_NAME]);
    }

    protected function insert(array $data): void
    {
        $params = [
            'index' => static::INDEX_NAME,
            'type'  => static::INDEX_TYPE ?? static::INDEX_NAME,
            'id'    => $data['id'],
            'body'  => $data,
        ];

        $response = $this->client->index($params);
    }
}
