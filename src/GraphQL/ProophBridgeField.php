<?php
declare(strict_types=1);

namespace Pac\ProophPackage\GraphQL;

use Pac\Middleware\IdentityMiddleware;
use Ramsey\Uuid\Uuid;
use Youshido\GraphQL\Execution\ResolveInfo\ResolveInfoInterface;
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

    public function resolve($value, array $args, ResolveInfoInterface $info)
    {
        $data = $this->resolveData($value, $args, $info);

        $this->messageFactory->createAndDispatchMessage(static::class, $data);
    }

    protected function resolveData($value, array $args, ResolveInfoInterface $info): array
    {
        if (empty($args[static::ID_FIELD])) {
            $id = Uuid::uuid4();
            $args += [static::ID_FIELD => $id->getHex()];
        } else {
            $id = Uuid::fromString($args[static::ID_FIELD]);
        }

        return [
//            IdentityMiddleware::IDENTITY_ATTRIBUTE => $info->getExecutionContext()->getContainer()->get(IdentityMiddleware::IDENTITY_ATTRIBUTE),
            'payload' => $args,
            'uuid'    => $id,
        ];
    }
}