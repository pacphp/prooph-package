<?php
declare(strict_types=1);

namespace Pac\ProophPackage\Projection;

use Diaclone\Connector\ElasticSearchConnector;
use Diaclone\ConnectorTransformService;
use Diaclone\Resource\Object;
use Elasticsearch\Client;
use Prooph\EventStore\Projection\AbstractReadModel;

abstract class AbstractElasticsearchModel extends AbstractReadModel
{
    const ID_NAME = 'id';
    const INDEX_TYPE = null;

    /** @var Client */
    protected $client;
    protected $connector;
    protected $transformer;

    public function __construct(Client $client, ConnectorTransformService $transformer)
    {
        $this->client = $client;
        $this->connector = new ElasticSearchConnector(
            $client,
            [
                'index' => static::INDEX_NAME,
                'type' => static::INDEX_TYPE ?? static::INDEX_NAME,
            ]
        );
        $this->transformer = $transformer;
    }

    public function create(array $data)
    {
        $transformerClass = static::TRANSFORMER_CLASS;
        $transformer = new $transformerClass();
        $resource = new Object($data);
        $object = $transformer->untransform($resource);
        $storableData = $transformer->transform(new Object($object));

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
        $this->connector->query(['match' => [static::ID_NAME => $id]]);

        $transformerClass = static::TRANSFORMER_CLASS;
        $this->transformer->untransform($this->connector, new $transformerClass(), Object::class);

        return $this->connector->getData();
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
        $this->connector->setData($object);
        $transformerClass = static::TRANSFORMER_CLASS;
        $this->transformer->transform($this->connector, new $transformerClass(), '', '*',Object::class);
    }

    public function saveObject($object)
    {
        $this->connector->setData($object);
        $transformerClass = static::TRANSFORMER_CLASS;
        $this->transformer->transform($this->connector, new $transformerClass(), '', '*',Object::class);
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
