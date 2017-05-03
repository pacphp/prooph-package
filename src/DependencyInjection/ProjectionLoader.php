<?php
declare(strict_types=1);

namespace Pac\ProophPackage\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class ProjectionLoader
{
    public function load($config, ContainerBuilder $containerBuilder): ContainerBuilder
    {
        $loader = new YamlFileLoader($containerBuilder, new FileLocator(__DIR__ . '/../config'));

        $loader->load('projection.yml');

        if (! empty($config['projection_managers'])) {
            $this->loadProjectionManagers($config, $containerBuilder);
        }

        if (! empty($config['projections'])) {
            $this->loadProjections($config, $containerBuilder);
        }

        return $containerBuilder;

    }

    protected function loadProjectionManagers(array $config, ContainerBuilder $containerBuilder)
    {
        foreach ($config['projection_managers'] as $projectionManagerName => $projectionManagerConfig) {
            $projectionManagerDefinition = new Definition();
            $projectionManagerDefinition
                ->setFactory([new Reference('prooph_event_store.projection_manager_factory'), 'createProjectionManager'])
                ->setArguments(
                    [
                        new Reference($projectionManagerConfig['event_store']),
                        new Reference($projectionManagerConfig['connection']),
                        $projectionManagerConfig['event_streams_table'] ?? 'event_streams',
                        $projectionManagerConfig['projections_table'] ?? 'projections',
                    ]
                );
            $projectorManagerId = sprintf('prooph_event_store.projection_manager.%s', $projectionManagerName);
            $containerBuilder->setDefinition(
                $projectorManagerId,
                $projectionManagerDefinition
            );
        }
    }

    protected function loadProjections(array $config, ContainerBuilder $containerBuilder)
    {
        foreach ($config['projections'] as $projectionName => $projectionConfig) {
            $containerBuilder->setAlias(sprintf('%s.%s.projection_manager', ProophExtension::TAG_PROJECTION, $projectionName), $projectionConfig['projection_manager']);
            $containerBuilder->setAlias(sprintf('%s.%s.read_model', ProophExtension::TAG_PROJECTION, $projectionName), $projectionConfig['read_model']);
            $containerBuilder
                ->setDefinition(
                    sprintf('prooph_event_store.projection.%s', $projectionName),
                    (new Definition())
                        ->setClass($projectionConfig['projection_class'])
                        ->addTag(
                            'prooph_event_store.projection',
                            [
                                'projection_name'    => $projectionName,
                                'read_model'         => $projectionConfig['read_model'],
                                'projection_manager' => $projectionConfig['projection_manager'],
                            ]
                        )
                );
        }
    }
}
