<?php

declare(strict_types=1);

namespace Emphloyer\Scheduler;

use Emphloyer\JobSerDes;
use IteratorIterator;
use Traversable;

class ScheduleEntryIterator extends IteratorIterator
{
    /** @var ScheduleEntrySerDes */
    protected $serDes;

    public function __construct(Traversable $source)
    {
        parent::__construct($source);
        $this->serDes = new ScheduleEntrySerDes(new JobSerDes());
    }

    /**
     * Convert an array with schedule entry attributes into an object
     */
    public function current() : ScheduleEntry
    {
        $attributes = parent::current();

        return $this->serDes->deserialize($attributes);
    }
}
