<?php

declare(strict_types=1);

namespace Emphloyer\Scheduler;

use Emphloyer\JobSerDes;

class ScheduleEntrySerDes
{
    /** @var JobSerDes */
    protected $jobSerDes;

    public function __construct(JobSerDes $jobSerDes)
    {
        $this->jobSerDes = $jobSerDes;
    }

    /**
     * @param mixed[] $attributes
     */
    public function deserialize(array $attributes) : ScheduleEntry
    {
        $job = $this->jobSerDes->deserialize($attributes['job']);

        return new ScheduleEntry(
            $job,
            $attributes['id'],
            $attributes['minute'],
            $attributes['hour'],
            $attributes['dayOfMonth'],
            $attributes['month'],
            $attributes['dayOfWeek']
        );
    }
}
