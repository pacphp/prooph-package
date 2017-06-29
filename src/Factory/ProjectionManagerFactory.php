<?php

declare(strict_types=1);

namespace Pac\ProophPackage\Factory;

use Prooph\EventStore\EventStore;
use Prooph\EventStore\Exception\RuntimeException;
use Prooph\EventStore\InMemoryEventStore;
use Prooph\EventStore\Pdo\MySqlEventStore;
use Prooph\EventStore\Pdo\PostgresEventStore;
use Prooph\EventStore\Pdo\Projection\MySqlProjectionManager;
use Prooph\EventStore\Pdo\Projection\PostgresProjectionManager;
use Prooph\EventStore\Projection\InMemoryProjectionManager;
use Prooph\EventStore\Projection\ProjectionManager;
use Prooph\EventStore\Projection\ReadModel;
use Prooph\EventStore\Projection\ReadModelProjector;
use Prooph\SnapshotStore\SnapshotStore;
use PDO;
use ReflectionProperty;

class ProjectionManagerFactory
{
    public function createProjectionManager(
        EventStore $eventStore,
        string $projectionsTable = 'projections'
    ): ProjectionManager {
        if ($eventStore instanceof InMemoryEventStore) {
            return new InMemoryProjectionManager($eventStore);
        }

        if ($eventStore instanceof PostgresEventStore) {
            $connection = static::getConnectorFromEventStore($eventStore);
            $eventStreamsTable = static::getEventStreamTableFromEventStore($eventStore);

            return new PostgresProjectionManager($eventStore, $connection, $eventStreamsTable, $projectionsTable);
        }

        if ($eventStore instanceof MySqlEventStore) {
            $connection = static::getConnectorFromEventStore($eventStore);
            $eventStreamsTable = static::getEventStreamTableFromEventStore($eventStore);

            return new MySqlProjectionManager($eventStore, $connection, $eventStreamsTable, $projectionsTable);
        }

        throw new RuntimeException(sprintf('ProjectionManager for %s not implemented.', $eventStore));
    }

    private function getConnectorFromEventStore(EventStore $eventStore): PDO
    {
        $rp = new ReflectionProperty($eventStore, 'connection');
        $rp->setAccessible(true);

        return $rp->getValue($eventStore);
    }

    private function getEventStreamTableFromEventStore(EventStore $eventStore): string
    {
        $rp = new ReflectionProperty($eventStore, 'eventStreamsTable');
        $rp->setAccessible(true);

        return $rp->getValue($eventStore);
    }
}
