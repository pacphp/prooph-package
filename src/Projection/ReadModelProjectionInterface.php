<?php
declare(strict_types=1);

namespace Pac\ProophPackage\Projection;

use Prooph\EventStore\Projection\ReadModelProjector;

interface ReadModelProjectionInterface
{
    public function project(ReadModelProjector $projector): ReadModelProjector;
}
