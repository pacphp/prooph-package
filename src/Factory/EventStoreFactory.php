<?php
declare(strict_types=1);

namespace Pac\ProophPackage\Factory;

use Prooph\EventStore\EventStore;
use Prooph\EventStore\Exception\ConfigurationException;
use Prooph\EventStore\Plugin\Plugin;

final class EventStoreFactory
{
    public function create(array $config, array $plugins = []): EventStore
    {
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

    }
}
