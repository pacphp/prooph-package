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

class ProjectionManagerFactory
{
    public function createProjectionManager(
        EventStore $eventStore,
        ?PDO $connection = null,
        string $eventStreamsTable = 'event_streams',
        string $projectionsTable = 'projections'
    ): ProjectionManager {

        $checkConnection = function () use ($connection) {
            if (!$connection instanceof PDO) {
                throw new RuntimeException('PDO connection missing');
            }
        };

        if ($eventStore instanceof InMemoryEventStore) {
            return new InMemoryProjectionManager($eventStore);
        }

        if ($eventStore instanceof PostgresEventStore) {
            $checkConnection();

            return new PostgresProjectionManager($eventStore, $connection, $eventStreamsTable, $projectionsTable);
        }

        if ($eventStore instanceof MySqlEventStore) {
            $checkConnection();

            return new MySqlProjectionManager($eventStore, $connection, $eventStreamsTable, $projectionsTable);
        }

        throw new RuntimeException(sprintf('ProjectionManager for %s not implemented.', $eventStore));
    }
}
