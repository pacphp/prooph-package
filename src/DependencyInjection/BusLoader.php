<?php
declare(strict_types=1);

namespace Pac\ProophPackage\DependencyInjection;

use Prooph\EventStoreBusBridge\TransactionManager;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\QueryBus;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class BusLoader
{
    const AVAILABLE_BUSES = [
        'event' => EventBus::class,
        'query' => QueryBus::class,
    ];

    public function load(string $name, array $options, ContainerBuilder $containerBuilder)
    {
        if (! empty($options['command_bus'])) {
            $this->loadCommandBus($name, $options['command_bus'], $containerBuilder);
        }
        if (! empty($options['event_bus'])) {
            $this->loadEventBus($name, $options['event_bus'], $containerBuilder);
        }
    }

    private function loadCommandBus(string $name, array $options, ContainerBuilder $containerBuilder)
    {
        $containerBuilder->setDefinition(
            'prooph_service_bus.' . $name . '_command_bus',
            new Definition(CommandBus::class)
        );  // option ActionEventEmitter

        $transactionPluginId = 'prooph_event_store_bus_bridge.' . $name . '_transaction_manager';
        $containerBuilder->setDefinition(
            $transactionPluginId,
            new Definition(
                TransactionManager::class,
                [
                    new Reference('prooph_event_store.' . $name . '_store')
                ]
            )
        );
        $options['plugins'][] = $transactionPluginId;

        $this->loadBus('command', $name, $options, $containerBuilder);
    }

    private function loadEventBus(string $name, array $options, ContainerBuilder $containerBuilder)
    {
        $containerBuilder->setDefinition(
            'prooph_service_bus.' . $name . '_event_bus',
            new Definition(EventBus::class)
        );

        $this->loadBus('event', $name, $options, $containerBuilder);
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
        $routerId = 'prooph_service_bus.' . $name . '_bus_router';
        $routerDefinition = new ChildDefinition('prooph_service_bus.' . $type . '_bus_router');
        $routerDefinition->setArguments([$options['routes'] ?? []]);
        $container->setDefinition($routerId, $routerDefinition);

        //Attach container plugin
        $containerPluginId = 'prooph_service_bus.container_plugin';
        $pluginIds = array_filter(array_merge($options['plugins'], [$containerPluginId, $messageFactoryPluginId, $routerId]));

        // Wrap the message bus creation into factory to call attachToMessageBus on the plugins
        $serviceBusDefinition
            ->setFactory([new Reference('prooph_service_bus.' . $type . '_bus_factory'), 'create'])
            ->setArguments(
                [
                    $container->getDefinition('prooph_service_bus.'.$type.'_bus')->getClass(),
                    new Reference('service_container'),
                    $pluginIds,
                ]
            );
    }
}
