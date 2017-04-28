<?php
declare(strict_types=1);

namespace Pac\ProophPackage\Factory;

use Prooph\ServiceBus\EventBus;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EventStoreFactory
{
    public function create(string $class, ContainerInterface $container, array $plugins = []): EventBus
    {
        /** @var EventBus $bus */
        $bus = new $class();
        foreach ($plugins as $pluginId) {
            $plugin = $container->get($pluginId);
            $plugin->a($bus);
        }

        return $bus;
    }
}
