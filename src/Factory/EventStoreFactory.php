<?php
declare(strict_types=1);

namespace Pac\ProophPackage\Factory;

use Prooph\Common\Messaging\FQCNMessageFactory;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\Exception\ConfigurationException;
use Prooph\EventStore\Pdo\MySqlEventStore;
use Prooph\EventStore\Pdo\PersistenceStrategy\MySqlSimpleStreamStrategy;
use Prooph\EventStore\Plugin\Plugin;

final class EventStoreFactory
{
    private $root;

    public function create(string $root, array $config, array $plugins = []): EventStore
    {
        $this->root = $root;

        $eventStore = $this->buildEventStore($config);

        foreach ($plugins as $plugin) {
            if (! $plugin instanceof Plugin) {
                throw ConfigurationException::configurationError(sprintf(
                    'Plugin %s does not implement the Plugin interface',
                    get_class($plugin)
                ));
            }
            $plugin->attachToEventStore($eventStore);
        }

        return $eventStore;
    }

    private function buildEventStore($config): EventStore
    {
        switch ($config['type']) {
            case 'mysql':
                $eventStore = $this->buildMySqlEventStore($config);

                break;

            default:
                throw ConfigurationException::configurationError($config['type'] . ' is not a known event store type');
        }

        return $eventStore;
    }

    private function buildMySqlEventStore($config): EventStore
    {
        switch ($config['strategy']) {
            case 'single_stream':
                $strategy = new MySqlSimpleStreamStrategy();

                break;
            default:
                throw ConfigurationException::configurationError($config['strategy']);
        }
        $messageFactory = new FQCNMessageFactory();
        $table = $this->root . '_streams';

        return new MySqlEventStore(
            $messageFactory,
            $config['connection'],
            $strategy,
            $config['load_batch_size'],
            $table
        );

    }
}
