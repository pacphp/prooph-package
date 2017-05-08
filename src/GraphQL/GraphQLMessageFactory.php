<?php
declare(strict_types=1);

namespace Pac\ProophPackage\GraphQL;

use DateTimeImmutable;
use DateTimeZone;
use Prooph\Common\Messaging\Message;
use Prooph\Common\Messaging\MessageFactory;
use Prooph\ServiceBus\CommandBus;
use Ramsey\Uuid\Uuid;

class GraphQLMessageFactory implements MessageFactory
{
    protected $commandBus;
    protected $fieldCommandMap;

    public function __construct(CommandBus $commandBus, array $fieldCommandMap)
    {
        $this->commandBus = $commandBus;
        $this->fieldCommandMap = $fieldCommandMap;
    }

    public function createAndDispatchMessage(string $fieldClass, array $messageData)
    {
        $command = $this->createMessageFromArray($fieldClass, $messageData);
        $this->commandBus->dispatch($command);
    }

    public function createMessageFromArray(string $fieldClass, array $messageData): Message
    {
        $messageName = $this->fieldCommandMap[$fieldClass];

        if (! isset($messageData['message_name'])) {
            $messageData['message_name'] = $messageName;
        }

        if (! isset($messageData['uuid'])) {
            $messageData['uuid'] = Uuid::uuid4();
        }

        if (! isset($messageData['created_at'])) {
            $messageData['created_at'] = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        }

        if (! isset($messageData['metadata'])) {
            $messageData['metadata'] = [];
        }

        return $messageName::fromArray($messageData);
    }
}
