<?php
declare(strict_types=1);

namespace Fixtures;

use Pac\ProophPackage\Projection\AbstractElasticsearchModel;

class ElasticsearchTestModel extends AbstractElasticsearchModel
{
    public const INDEX_NAME = 'abstract_test_index_';

    public function init(): void
    {
        $params = [
            'index' => self::INDEX_NAME,
        ];

        $response = $this->client->indices()->create($params);
    }
}
