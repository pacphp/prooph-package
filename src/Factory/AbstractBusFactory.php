<?php
declare(strict_types=1);

namespace Pac\ProophPackage\Factory;

use Prooph\ServiceBus\Exception\RuntimeException;
use Prooph\ServiceBus\MessageBus;
use Prooph\ServiceBus\Plugin\Router\AsyncSwitchMessageRouter;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractBusFactory
{
    protected $root;

    public function create(string $root, array $config, ContainerInterface $container): MessageBus
    {
        $this->root = $root;

        /** @var MessageBus $bus */
        $busClass = $this->getBusClass();
        $bus = new $busClass();

        if (isset($config['plugins'])) {
            $this->attachPlugins($bus, $config['plugins'], $container);
        }

        if (isset($config['routes'])) {
            $this->attachRouter($bus, $config, $config['routes'], $container);
        }

        return $bus;
    }

    /**
     * Returns the FQCN of a bus extending Prooph\ServiceBus\MessageBus
     */
    abstract protected function getBusClass(): string;

    /**
     * Returns the default router class to use if no one was specified in the config
     */
    abstract protected function getDefaultRouterClass(): string;

    private function attachPlugins(MessageBus $bus, array $plugins, ContainerInterface $container): void
    {
        foreach ($plugins as $index => $plugin) {
            if (! is_string($plugin) || ! $container->has($plugin)) {
                throw new RuntimeException(sprintf(
                    'Misconfigured %s bus plugin %s for %s.',
                    static::TYPE, $plugin, $this->root
                ));
            }

            $container->get($plugin)->attachToMessageBus($bus);
        }
    }

    private function attachRouter(MessageBus $bus, array $config, array $routes, ContainerInterface $container): void
    {
        $routerClass = $config['type'] ?? $this->getDefaultRouterClass();

        $loadedRoutes = [];

        foreach ($routes as $messageName => $handler) {
            $loadedRoutes[$messageName] = $container->get($handler);
        }
        $router = new $routerClass($routes);
        $router = new $routerClass($loadedRoutes);

        if (isset($config['async_switch'])) {
            $asyncMessageProducer = $container->get($config['async_switch']);

            $router = new AsyncSwitchMessageRouter($router, $asyncMessageProducer);
        }

        $router->attachToMessageBus($bus);
    }
}
