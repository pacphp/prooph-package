<?php
declare(strict_types=1);

namespace Pac\ProophPackage\EventSourcing;

use Prooph\EventSourcing\AggregateChanged;
use Prooph\EventSourcing\AggregateRoot as ProophAggregateRoot;

abstract class AggregateRoot extends ProophAggregateRoot
{
    protected $id;

    protected function aggregateId(): string
    {
        return $this->id;
    }

    /**
     * In child class set up a whenSomeEvent() to handle a SomeEvent
     *
     * @param AggregateChanged $event
     */
    protected function apply(AggregateChanged $event): void
    {
        $method = 'when' . substr(strrchr(get_class($event), '\\'), 1);

        $this->$method($event);
    }
}
