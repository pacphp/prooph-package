<?php
declare(strict_types=1);

namespace Test\Functional;

use Carbon\Carbon;
use DateTimeZone;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Fixtures\ElasticsearchTestModel;
use Fixtures\Test;
use FunctionalTester;
use ReflectionMethod;

class AbstractElasticsearchModelCest
{
    /** @var Client */
    protected $client;
    protected $id = '85c8d412bc844da1985482a59ac79806';

    /** @var ElasticsearchTestModel */
    protected $testModel;

    public function __construct()
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
        $data = [
            'id' => $this->id,
            'message' => 'trying out Elasticsearch',
            'post_date' => '2009-11-15T14:12:12',
            'user' => 'kimchy',
        ];
        $rm = new ReflectionMethod(ElasticsearchTestModel::class, 'insert');
        $rm->setAccessible(true);
        $rm->invoke($this->testModel, $data);

        $params = [
            'index' => ElasticsearchTestModel::INDEX_NAME,
            'type' => ElasticsearchTestModel::INDEX_NAME,
            'id' => $this->id,
        ];

        $document = $this->client->get($params);
        $I->assertTrue($document['found']);
    }

    /**
     * @depends testInsert
     */
    public function testFindObjectById(FunctionalTester $I)
    {
        sleep(1);
        $object = $this->testModel->findObjectById($this->id);
        $expected = (new Test())
            ->setId($this->id)
            ->setMessage('trying out Elasticsearch')
            ->setPostDate(new Carbon('2009-11-15T14:12:12'))
            ->setUser('kimchy');
        $I->assertEquals($expected, $object);
    }

    /**
     * @depends testFindObjectById
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
                ],
            ],
        ];
        sleep(1);
        $response = $this->client->search($params);
        $I->assertSame(0, $response['hits']['total']);
    }

    /**
     * @depends testReset
     */
    public function testInsertObject(FunctionalTester $I)
    {
        $test = (new Test())
            ->setId($this->id)
            ->setMessage('trying out Elasticsearch')
            ->setPostDate(new Carbon('2009-11-15T14:12:12', new DateTimeZone('UTC')))
            ->setUser('kimchy');

        $this->testModel->insertObject($test);
        sleep(1);
        $results = $this->client->search(['body' => ['query' => ['match' => [ElasticsearchTestModel::ID_NAME => $this->id]]]]);
        $I->assertSame(1, $results['hits']['total']);
        $expected = [
            'id' => $this->id,
            'message' => 'trying out Elasticsearch',
            'post_date' => '2009-11-15T14:12:12+0000',
            'user' => 'kimchy',
        ];
        $I->assertSame($expected, $results['hits']['hits'][0]['_source']);
    }

    /**
     * @depends testInsertObject
     */
    public function testSaveObject(FunctionalTester $I)
    {
        $test = (new Test())
            ->setId($this->id)
            ->setMessage('changing the message')
            ->setPostDate(new Carbon('2009-11-15T14:12:12', new DateTimeZone('UTC')))
            ->setUser('kimchy');

        $this->testModel->saveObject($test);
        sleep(1);
        $results = $this->client->search(['body' => ['query' => ['match' => [ElasticsearchTestModel::ID_NAME => $this->id]]]]);
        $I->assertSame(1, $results['hits']['total']);
        $expected = [
            'id' => $this->id,
            'message' => 'changing the message',
            'post_date' => '2009-11-15T14:12:12+0000',
            'user' => 'kimchy',
        ];
        $I->assertSame($expected, $results['hits']['hits'][0]['_source']);
    }

    /**
     * @depends testSaveObject
     */
    public function testDelete(FunctionalTester $I)
    {
        $this->testModel->delete();
        $I->assertFalse($this->client->indices()->exists(['index' => ElasticsearchTestModel::INDEX_NAME]));
    }
}
