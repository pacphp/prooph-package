<?php
declare(strict_types=1);

namespace Pac\ProophPackage\Factory;

use Elasticsearch\ClientBuilder;

class ElasticsearchClientFactory
{
    public static function create(array $hosts)
    {
        return ClientBuilder::create()
            ->setHosts($hosts)
            ->build();
    }
}
