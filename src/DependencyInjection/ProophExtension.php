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
    public const TAG_PROJECTION = 'prooph_event_store.projection';

    public function load(array $configs, ContainerBuilder $containerBuilder)
    {
        $loader = new YamlFileLoader($containerBuilder, new FileLocator(__DIR__ . '/../config'));
        $loader->load('event_store.yml');
        $loader->load('command_bus.yml');
        $loader->load('event_bus.yml');
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
            (new ProjectionLoader())->load($options['projection'], $containerBuilder);
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
}
