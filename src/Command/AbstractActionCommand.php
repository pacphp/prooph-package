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
    const ID_FIELD = 'id';

    use PayloadTrait;

    public static function fromPayload($payload): Command
    {
        if (empty($payload[static::ID_FIELD])) {
            $uuid = Uuid::uuid4();
            $payload += [static::ID_FIELD => $uuid->getHex()];
        } else {
            $uuid = Uuid::fromString($payload[static::ID_FIELD]);
        }

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

}
