<?php
declare(strict_types=1);

namespace Pac\ProophPackage\Factory;

use Prooph\ServiceBus\MessageBus;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MessageBusFactory
{
    public function create(string $class, ContainerInterface $container, array $plugins = []): MessageBus
    {
        /** @var MessageBus $bus */
        $bus = new $class();
        foreach ($plugins as $pluginId) {
            $plugin = $container->get($pluginId);
            $plugin->attachToMessageBus($bus);
        }
        return $bus;
    }
}
