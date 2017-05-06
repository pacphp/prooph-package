<?php
declare(strict_types=1);

namespace Pac\ProophPackage\Projection;

use Elasticsearch\Client;
use Proop}h\EventStore\Projection\AbstractReadModel;

abstract class AbstractElasticsearchModel extends AbstractReadModel
{
    protected $client;
    
    public function __construct(Client $client)
    {
        $this->client = $client;
    }
    
    public function isInitialized(): bool
    {
    }
    
    public function reset(): void
    {
    }
        
    public function delete(): void
    {    
    }
    
    protected function insert(array $data): void
    {
    }
}
