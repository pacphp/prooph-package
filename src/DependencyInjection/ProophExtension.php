<?php
declare(strict_types=1);

namespace Pac\ProophPackage\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ProophExtension implements ExtensionInterface
{
    public const TAG_PROJECTION = 'prooph_event_store.projection';

    public function load(array $configs, ContainerBuilder $containerBuilder)
    {
        $loader = new YamlFileLoader($containerBuilder, new FileLocator(__DIR__ . '/../config'));
        $loader->load('event_store.yml');
        $loader->load('projection.yml');
        $loader->load('service_bus.yml');

        $config = call_user_func_array('array_merge', $configs);
        foreach ($config as $root => $options) {
            // order matters with the loader?
            (new EventStoreLoader())->load($root, $options['event_store'], $containerBuilder);
            (new BusLoader())->load($root, $options, $containerBuilder);
            if (! empty($options['graphql'])) {
                (new GraphQLLoader())->load($root, $options['graphql'], $containerBuilder);
            }
            (new ProjectionLoader())->load($root, $options['projection'], $containerBuilder);
        }

        return $containerBuilder;
    }

    public function getNamespace()
    {
        // TODO: Implement getNamespace() method.
    }

    public function getXsdValidationBasePath()
    {
        // TODO: Implement getXsdValidationBasePath() method.
    }

    public function getAlias()
    {
        return 'prooph';
    }
}
