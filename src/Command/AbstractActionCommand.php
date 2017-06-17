<?php
declare(strict_types=1);

namespace Pac\ProophPackage\Command;

use DateTimeImmutable;
use DateTimeZone;
use Prooph\Common\Messaging\Command;
use Prooph\Common\Messaging\PayloadConstructable;
use Prooph\Common\Messaging\PayloadTrait;
use Ramsey\Uuid\Uuid;

abstract class AbstractActionCommand extends Command implements PayloadConstructable
{
    use PayloadTrait;

    public function getAggregateId(): string
    {
        return $this->payload[static::rootIdFieldName()];
    }

    public static function fromPayload($payload): Command
    {
        $uuid = Uuid::fromString($payload[static::rootIdFieldName()]);

        $createdAt = new DateTimeImmutable('now', new DateTimeZone('UTC'));

        $message = [
            'created_at'   => $createdAt,
            'message_name' => static::class,
            'metadata'     => [],
            'payload'      => $payload,
            'uuid'         => $uuid,
        ];

        return static::fromArray($message);
    }

    abstract protected static function rootIdFieldName(): string;
}
