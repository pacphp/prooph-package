<?php
declare(strict_types=1);

namespace Pac\ProophPackage\EventSourcing;

use Prooph\EventSourcing\AggregateChanged as ProophAggregateChanged;

class AggregateChanged extends ProophAggregateChanged
{
    public static function forRecord($messageData)
    {
        return new static($messageData['uuid'], $messageData['payload']);
    }
}
