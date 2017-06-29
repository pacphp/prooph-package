<?php
declare(strict_types=1);

namespace Pac\ProophPackage\Factory;

use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\Plugin\Router\CommandRouter;

class CommandBusFactory extends AbstractBusFactory
{
    CONST TYPE = 'command';

    protected function getBusClass(): string
    {
        return CommandBus::class;
    }

    protected function getDefaultRouterClass(): string
    {
        return CommandRouter::class;
    }
}
