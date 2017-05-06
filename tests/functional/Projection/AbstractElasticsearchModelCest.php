<?php
declare(strict_types=1);

namespace Test\Functional;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Fixtures\ElasticsearchTestModel;
use FunctionalTester;
use ReflectionMethod;

class AbstractElasticsearchModelCest
{
    /** @var Client */
    protected $client;
    /** @var ElasticsearchTestModel */
    protected $testModel;

    public function _before(FunctionalTester $I)
    {
        $this->client = ClientBuilder::create()->build();

        $this->testModel = new ElasticsearchTestModel($this->client);
    }

    public function testIsInitialized(FunctionalTester $I)
    {
        $params = [
            'index' => ElasticsearchTestModel::INDEX_NAME,
        ];

        if ($this->client->indices()->exists($params)) {
            $this->client->indices()->delete($params);
        }
        $I->assertFalse($this->testModel->isInitialized());
    }

    /**
     * @depends testIsInitialized
     */
    public function testInit(FunctionalTester $I)
    {
        $this->testModel->init();
        $I->assertTrue($this->client->indices()->exists(['index' => ElasticsearchTestModel::INDEX_NAME]));
    }

    /**
     * @depends testInit
     */
    public function testInsert(FunctionalTester $I)
    {
        $id = '85c8d412bc844da1985482a59ac79806';
        $data = [
            'id'        => $id,
            'message'   => 'trying out Elasticsearch',
            'post_date' => '2009-11-15T14:12:12',
            'user'      => 'kimchy',
        ];
        $rm = new ReflectionMethod(ElasticsearchTestModel::class, 'insert');
        $rm->setAccessible(true);
        $rm->invoke($this->testModel, $data);

        $params = [
            'index' => ElasticsearchTestModel::INDEX_NAME,
            'type' => ElasticsearchTestModel::INDEX_NAME,
            'id' => $id,
        ];

        $document = $this->client->get($params);
        $I->assertTrue($document['found']);
    }

    /**
     * @depends testInsert
     */
    public function testReset(FunctionalTester $I)
    {
        $this->testModel->reset();
        $I->assertTrue($this->client->indices()->exists(['index' => ElasticsearchTestModel::INDEX_NAME]));
        $params = [
            "index" => ElasticsearchTestModel::INDEX_NAME,
            "body" => [
                "query" => [
                    "match_all" => new \stdClass(),
                ]
            ]
        ];
        $response = $this->client->search($params);
        $I->assertSame(0, $response['hits']['total']);
    }

    /**
     * @depends testReset
     */
    public function testDelete(FunctionalTester $I)
    {
        $this->testModel->delete();
        $I->assertFalse($this->client->indices()->exists(['index' => ElasticsearchTestModel::INDEX_NAME]));
    }
}
