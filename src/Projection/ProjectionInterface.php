<?php
declare(strict_types=1);

namespace Pac\ProophPackage\Projection;

use Prooph\EventStore\Projection\Projector;

interface ProjectionInterface
{
    public function project(Projector $projector): Projector;
}
