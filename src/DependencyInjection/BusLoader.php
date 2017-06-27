<?php
declare(strict_types=1);

namespace Pac\ProophPackage\DependencyInjection;

use Prooph\EventStoreBusBridge\TransactionManager;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\QueryBus;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class BusLoader
{
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

        $containerBuilder->setDefinition(
            'prooph_service_bus.' . $name . '_command_bus',
            new Definition(CommandBus::class)
        );  // option ActionEventEmitter

        $containerBuilder
            ->setDefinition(
                'prooph_service_bus.' . $name . '_command_bus',
                new Definition(CommandBus::class)
            )
            ->setFactory([new Reference('prooph_service_bus.command_bus_factory'), 'create'])
            ->setArguments(
                [
                    $name,
                    $options,
                    new Reference('service_container')
                ]
            );
    }

    private function loadEventBus(string $name, array $options, ContainerBuilder $containerBuilder)
    {
        $containerBuilder->setDefinition(
            'prooph_service_bus.' . $name . '_event_bus',
            new Definition(EventBus::class)
        );

//        $this->loadBus('event', $name, $options, $containerBuilder);
    }
}
