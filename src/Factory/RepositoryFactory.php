<?php
declare(strict_types=1);

namespace Pac\ProophPackage\Factory;

use Prooph\EventSourcing\Aggregate\AggregateTranslator;
use Prooph\EventSourcing\Aggregate\AggregateType;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\StreamName;
use Prooph\SnapshotStore\SnapshotStore;

class RepositoryFactory
{
    public function create(
        string $repositoryClass,
        EventStore $eventStore,
        string $aggregateType,
        AggregateTranslator $aggregateTranslator,
        SnapshotStore $snapshotStore = null,
        string $streamName = null,
        bool $oneStreamPerAggregate = false
    ) {
        return new $repositoryClass(
            $eventStore,
            AggregateType::fromAggregateRootClass($aggregateType),
            $aggregateTranslator,
            $snapshotStore,
            $streamName ? new StreamName($streamName) : null,
            $oneStreamPerAggregate
        );
    }
}
