<?php
declare(strict_types=1);

namespace Pac\ProophPackage\GraphQL;

use DateTimeImmutable;
use DateTimeZone;
use Pac\ProophPackage\Command\AbstractActionCommand;
use Prooph\Common\Messaging\Command;
use Prooph\Common\Messaging\Message;
use Prooph\Common\Messaging\MessageFactory;
use Prooph\ServiceBus\CommandBus;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class GraphQLMessageFactory implements MessageFactory
{
    protected $commandBus;
    /** @var AbstractActionCommand[] */
    protected $fieldCommandMap;

    public function __construct(CommandBus $commandBus, array $fieldCommandMap)
    {
        $this->commandBus = $commandBus;
        $this->fieldCommandMap = $fieldCommandMap;
    }

    public function createAndDispatchMessage(string $fieldClass, array $messageData): UuidInterface
    {
        $command = $this->createMessageFromArray($fieldClass, $messageData);
        $this->commandBus->dispatch($command);

        return $command->uuid();
    }

    public function createMessageFromArray(string $fieldClass, array $messageData): Message
    {
        $messageName = $this->fieldCommandMap[$fieldClass];

        return $messageName::fromPayload($messageData);
    }
}
