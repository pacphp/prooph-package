<?php
declare(strict_types=1);

namespace Pac\ProophPackage\DependencyInjection;

use Prooph\EventStoreBusBridge\EventPublisher;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class EventStoreLoader
{
    public function load(string $name, array $options, ContainerBuilder $container)
    {
//        if (! empty($options['event_bus'])) {
//            $plugins[] = $container
//                ->setDefinition(
//                    'prooph_event_store_bus_bridge.company_event_publisher',
//                    new Definition(
//                        EventPublisher::class,
//                        [new Reference($options['event_bus'])])
//                );
//        }
//        unset($options['event_bus']);

        $plugins = [];
        if (! empty($options['plugins'])) {
            foreach ($options['plugins'] as $pluginAlias) {
                $plugin = new Reference($pluginAlias);
                $plugins[] = $plugin;
            }
        }

        $arguments = $options;
        if (! empty($options['connection'])) {
            $arguments['connection'] = new Reference($options['connection']);
        }

//        $arguments['event_emitter'] = new Reference($options['event_emitter'] ?? 'prooph_event_store.action_event_emitter'),
        unset($arguments['plugins']);
        unset($arguments['repositories']);

        $eventStoreId = static::eventStoreId($name);
        $container
            ->setDefinition(
                $eventStoreId,
                new ChildDefinition('prooph_event_store.store_definition')
            )
            ->setFactory([new Reference('prooph_event_store.store_factory'), 'create'])
            ->setArguments(
                [
                    $name,
                    $arguments,
                    $plugins,
                ]
            );

        if (! empty($options['repositories'])) {
            foreach ($options['repositories'] as $repositoryName => $repositoryConfig) {
                $streamName = null;
                if (!isset($repositoryConfig['stream_name'])) {
                    $streamName = $name . '_streams';
                }
                $repositoryDefinition = $container
                    ->setDefinition(
                        $repositoryName,
                        new ChildDefinition('prooph_event_store.repository_definition')
                    )
                    ->setFactory([new Reference('prooph_event_store.repository_factory'), 'create'])
                    ->setArguments(
                        [
                            $repositoryConfig['repository_class'],
                            new Reference($eventStoreId),
                            $repositoryConfig['aggregate_type'],
                            new Reference('prooph_event_sourcing.aggregate_translator'),
                            isset($repositoryConfig['snapshot_store']) ? new Reference($repositoryConfig['snapshot_store']) : null,
                            $streamName,
                            $repositoryConfig['one_stream_per_aggregate'] ?? false,
                        ]
                    );
            }
        }

        // define metadata enrichers
        $metadataEnricherAggregateId = sprintf('prooph_event_store.%s.%s', 'metadata_enricher_aggregate', $name);

        $metadataEnricherAggregateDefinition = $container
            ->setDefinition(
                $metadataEnricherAggregateId,
                new ChildDefinition('prooph_event_store.metadata_enricher_aggregate_definition')
            )
            ->setClass('%prooph_event_store.metadata_enricher_aggregate.class%');

        $metadataEnricherId = sprintf('prooph_event_store.%s.%s', 'metadata_enricher_plugin', $name);

        $metadataEnricherDefinition = $container
            ->setDefinition(
                $metadataEnricherId,
                new ChildDefinition('prooph_event_store.metadata_enricher_plugin_definition')
            )
            ->setClass('%prooph_event_store.metadata_enricher_plugin.class%');
    }

    public static function eventStoreId(string $name): string
    {
        return 'prooph_event_store.' . $name . '_store';
    }
}
