<?php
declare(strict_types=1);

namespace Fixtures;

use Diaclone\Transformer\AbstractObjectTransformer;
use Diaclone\Transformer\DateTimeIso8601Transformer;

class TestTransformer extends AbstractObjectTransformer
{
    protected static $mappedProperties = [
        'id'       => 'id',
        'message'  => 'message',
        'postDate' => 'post_date',
        'user'     => 'user',
    ];

    protected static $transformers = [
        'postDate' => DateTimeIso8601Transformer::class,
    ];

    public function getObjectClass(): string
    {
        return Test::class;
    }
}