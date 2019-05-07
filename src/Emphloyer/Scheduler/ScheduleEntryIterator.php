<?php

namespace Emphloyer\Scheduler;

class ScheduleEntryIterator extends \IteratorIterator
{
    /**
     * @var \Emphloyer\Scheduler\ScheduleEntrySerDes
     */
    protected $serDes;

    /**
     * @param \Traversable $source
     */
    public function __construct(\Traversable $source)
    {
        parent::__construct($source);
        $this->serDes = new \Emphloyer\Scheduler\ScheduleEntrySerDes(new \Emphloyer\JobSerDes());
    }

    /**
     * Convert an array with schedule entry attributes into an object
     * @return \Emphloyer\Scheduler\ScheduleEntry
     */
    public function current()
    {
        $attributes = parent::current();
        return $this->serDes->deserialize($attributes);
    }
}
