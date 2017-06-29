<?php
declare(strict_types=1);

namespace Pac\ProophPackage\DependencyInjection;

use Pac\ProophPackage\GraphQL\GraphQLMessageFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class GraphQLLoader
{
    public function load(string $name, array $options, ContainerBuilder $containerBuilder)
    {
        $containerBuilder->setDefinition(
            'prooph.graphql_message_factory.' . $name,
            new Definition(
                GraphQLMessageFactory::class,
                [
                    new Reference('prooph_service_bus.' . $name . '_command_bus'),
                    $options['routes']
                ]
            )
        );
    }
}
