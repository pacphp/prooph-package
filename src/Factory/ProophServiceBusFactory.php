<?php
declare(strict_types=1);

namespace Pac\ProophPackage\Factory;

use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\Plugin\Router\CommandRouter;
use Prooph\ServiceBus\Plugin\ServiceLocatorPlugin;

class ProophServiceBusFactory
{
    public static function create(array $config)
    {
        /** @var CommandBus $commandBus */
        $commandBus = new $config['command_bus']['class'];

        foreach ($plugins as $pluginId) {
            $plugin = $container->get($pluginId);
            $plugin->attachToMessageBus($commandBus);
        }

        $plugins =
            [
                0 => 'prooph_event_store_bus_bridge.transaction_manager',
                2 => 'prooph_service_bus.message_factory_plugin.todo_command_bus',
            ];
        (new ServiceLocatorPlugin($container))->attachToMessageBus($commandBus);

        /** @var CommandRouter $router */
        $router = new $config['command_bus']['router']['class'];
        foreach ($config['command_bus']['router']['routes'] as $command => $handler) {
            $router->route($command)->to($handler);
        }
        $router->attachToMessageBus($commandBus);

        return $commandBus;
    }
}
