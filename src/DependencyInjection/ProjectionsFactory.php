<?php
declare(strict_types=1);

namespace Pac\ProophPackage\DependencyInjection;

class ProjectionsFactory
{
    public function loadProjectors($class, $projectors)
    {
        $projector = new $class();

        foreach ($projectors as $event => $projections) {
            foreach ($projections as $projection) {
                $projector->addProjection($event, $projection);
            }
        }

        return $projector;
    }
}
