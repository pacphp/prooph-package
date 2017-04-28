<?php
declare(strict_types=1);

namespace Pac\ProophPackage\DependencyInjection;

use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\QueryBus;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class ProophExtension implements ExtensionInterface
{
    const AVAILABLE_BUSES = [
        'command' => CommandBus::class,
        'event' => EventBus::class,
        'query' => QueryBus::class,
    ];

    public function load(array $configs, ContainerBuilder $containerBuilder)
    {
        $config = call_user_func_array('array_merge', $configs);

        $loader = new YamlFileLoader($containerBuilder, new FileLocator(__DIR__ . '/../config'));

        $loader->load('event_store.yml');
        foreach ($config['stores'] as $name => $options) {
            $this->loadEventStore($name, $options, $containerBuilder);
        }

        $loader->load('service_bus.yml');
        foreach (self::AVAILABLE_BUSES as $type => $bus) {
            if (! empty($config[$type . '_buses'])) {
                $this->busLoad($type, $bus, $config[$type . '_buses'], $containerBuilder, $loader);
            }
        }

        return $containerBuilder;
    }

    /**
     * Returns the namespace to be used for this extension (XML namespace).
     *
     * @return string The XML namespace
     */
    public function getNamespace()
    {
        // TODO: Implement getNamespace() method.
    }

    /**
     * Returns the base path for the XSD files.
     *
     * @return string The XSD base path
     */
    public function getXsdValidationBasePath()
    {
        // TODO: Implement getXsdValidationBasePath() method.
    }

    /**
     * Returns the recommended alias to use in XML.
     *
     * This alias is also the mandatory prefix to use when using YAML.
     *
     * @return string The alias
     */
    public function getAlias()
    {
        return 'prooph';
    }

    private function loadEventStore(string $name, array $options, ContainerBuilder $container)
    {
        $eventStoreId = 'prooph_event_store.' . $name;
        $eventStoreDefinition = $container
            ->setDefinition(
                $eventStoreId,
                new ChildDefinition('prooph_event_store.store_definition')
            )
            ->setFactory([new Reference('prooph_event_store.store_factory'), 'create'])
            ->setArguments(
                [
                    $name,
                    new Reference($options['type']),
                    new Reference($options['event_emitter'] ?? 'prooph_event_store.action_event_emitter'),
                    new Reference('service_container'),
                ]
            );

        if (! empty($options['repositories'])) {
            foreach ($options['repositories'] as $repositoryName => $repositoryConfig) {
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
                            new Reference($repositoryConfig['aggregate_translator']),
                            isset($repositoryConfig['snapshot_store']) ? new Reference($repositoryConfig['snapshot_store']) : null,
                            $repositoryConfig['stream_name'] ?? null,
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

    private function busLoad(
        string $type,
        string $class,
        array $config,
        ContainerBuilder $container,
        YamlFileLoader $loader
    ) {
        // load specific bus configuration e.g. command_bus
        $loader->load($type . '_bus.yml');

        $serviceBuses = [];
        foreach (array_keys($config) as $name) {
            $serviceBuses[$name] = 'prooph_service_bus.' . $name;
        }
        $container->setParameter('prooph_service_bus.' . $type . '_buses', $serviceBuses);

        $def = $container->getDefinition('prooph_service_bus.' . $type . '_bus');
        $def->setClass($class);

        foreach ($config as $name => $options) {
            $this->loadBus($type, $name, $options, $container);
        }
    }

    private function loadBus(string $type, string $name, array $options, ContainerBuilder $container)
    {
        $serviceBusId = 'prooph_service_bus.' . $name;
        $serviceBusDefinition = $container->setDefinition(
            $serviceBusId,
            new ChildDefinition('prooph_service_bus.' . $type . '_bus')
        );
        // define message factory
        $messageFactoryId = 'prooph_service_bus.message_factory.'.$name;
        $container->setDefinition(
            $messageFactoryId,
            new ChildDefinition($options['message_factory'] ?? 'prooph_service_bus.message_factory')
        );

        // define message factory plugin
        $messageFactoryPluginId = 'prooph_service_bus.message_factory_plugin.'.$name;
        $messageFactoryPluginDefinition = new ChildDefinition('prooph_service_bus.message_factory_plugin');
        $messageFactoryPluginDefinition->setArguments([new Reference($messageFactoryId)]);
        $messageFactoryPluginDefinition->setPublic(true);

        $container->setDefinition(
            $messageFactoryPluginId,
            $messageFactoryPluginDefinition
        );

        // define router
        $routerId = null;
        if (! empty($options['router'])) {
            $routerId = 'prooph_service_bus.' . $name . '.router';
            $routerDefinition = new ChildDefinition($options['router']['type']);
            $routerDefinition->setArguments([$options['router']['routes'] ?? []]);
            $routerDefinition->setPublic(true);
            $container->setDefinition($routerId, $routerDefinition);
        }

        //Attach container plugin
        $containerPluginId = 'prooph_service_bus.container_plugin';
        $pluginIds = array_filter(array_merge($options['plugins'], [$containerPluginId, $messageFactoryPluginId, $routerId]));

        // Wrap the message bus creation into factory to call attachToMessageBus on the plugins
        $serviceBusDefinition
            ->setFactory([new Reference('prooph_service_bus.'.$type.'_bus_factory'), 'create'])
            ->setArguments(
                [
                    $container->getDefinition('prooph_service_bus.'.$type.'_bus')->getClass(),
                    new Reference('service_container'),
                    $pluginIds,
                ]
            );
    }
}
