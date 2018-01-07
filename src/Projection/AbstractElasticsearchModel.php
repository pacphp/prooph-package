<?php
declare(strict_types=1);

namespace Pac\ProophPackage\Projection;

use Diaclone\Resource\ObjectItem;
use Elasticsearch\Client;
use Prooph\EventStore\Projection\AbstractReadModel;

abstract class AbstractElasticsearchModel extends AbstractReadModel
{
    const ID_NAME = 'id';
    const INDEX_TYPE = null;

    /** @var Client */
    protected $client;
    protected $connector;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function create(array $data)
    {
        $transformerClass = static::TRANSFORMER_CLASS;
        $transformer = new $transformerClass();
        $resource = new ObjectItem($data);
        $object = $transformer->untransform($resource);
        $storableData = $transformer->transform(new ObjectItem($object));

        $this->insert($storableData);
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function isInitialized(): bool
    {
        return $this->client->indices()->exists(['index' => static::INDEX_NAME]);
    }

    public function findObjectById(string $id)
    {
        $results = $this->client->search(['body' => ['query' => ['match' => [static::ID_NAME => $id]]]]);
        if (0 === $results['hits']['total']) {
            return null;
        }
        $hit = $results['hits']['hits'][0];

        $transformerClass = static::TRANSFORMER_CLASS;
        $resource = new ObjectItem($hit['_source']);

        return (new $transformerClass())->toObject($resource);
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

    public function insertObject($object)
    {
        $transformerClass = static::TRANSFORMER_CLASS;
        $resource = new ObjectItem($object);

        $data = (new $transformerClass())->toArray($resource);

        $this->insert($data);
   }

    public function saveObject($object)
    {
        $this->insertObject($object);
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
