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
    public function load(string $name, array $config, ContainerBuilder $containerBuilder): ContainerBuilder
    {
        $loader = new YamlFileLoader($containerBuilder, new FileLocator(__DIR__ . '/../config'));

        $loader->load('projection.yml');

        $containerBuilder->setAlias(sprintf('%s.%s.read_model_collection', ProophExtension::TAG_PROJECTION, $name), $config['read_model_collection']);

        $eventStore = $config['event_store'] ?? EventStoreLoader::eventStoreId($name);
        $projectionManagerDefinition = new Definition();
        $projectionManagerDefinition
            ->setFactory([new Reference('prooph_event_store.projection_manager_factory'), 'createProjectionManager'])
            ->setArguments(
                [
                    new Reference($eventStore),
                ]
            );
        $projectorManagerId = static::projectionManagerId($name);
        $containerBuilder->setDefinition(
            $projectorManagerId,
            $projectionManagerDefinition
        );

        if (! empty($config['projectors'])) {
            $this->loadProjections($name, $config, $containerBuilder);
        }

        return $containerBuilder;
    }

    public static function projectionManagerId(string $name): string
    {
        return sprintf('%s.%s.projection_manager', ProophExtension::TAG_PROJECTION, $name);
    }

    public static function readModelId(string $name): string
    {
        return sprintf('%s.%s.read_model_collection', ProophExtension::TAG_PROJECTION, $name);
    }

    protected function loadProjections(string $name, array $config, ContainerBuilder $containerBuilder)
    {
        $referencedProjectors = [];
        foreach ($config['projectors'] as $event => $projectors) {
            foreach ($projectors as $projector) {
                $referencedProjectors[$event][] = new Reference($projector);
            }
        }
        $containerBuilder
            ->setDefinition(
                sprintf('prooph_event_store.projection.%s', $name),
                (new Definition($config['projection_class']))
                    ->setFactory([ProjectionsFactory::class, 'loadProjectors'])
                    ->setArguments(
                        [
                            $config['projection_class'],
                            $referencedProjectors,
                        ]
                    )
            );
    }
}
