<?php
declare(strict_types=1);

namespace Pac\ProophPackage\GraphQL;

use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Field\AbstractField;

abstract class ProophBridgeField extends AbstractField
{
    const ID_FIELD = 'id';

    protected $messageFactory;

    public function __construct(GraphQLMessageFactory $messageFactory, array $config = [])
    {
        $this->messageFactory = $messageFactory;
        parent::__construct($config);
    }

    public function resolve($value, array $args, ResolveInfo $info)
    {
        $data = $this->resolveData($value, $args, $info);
        $data['rootId'] = $this->messageFactory->createAndDispatchMessage(static::class, $data);

        return $data;
    }

    abstract protected function resolveData($value, array $args, ResolveInfo $info): array;
}
