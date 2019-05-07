<?php

namespace Emphloyer\Scheduler;

class ScheduleEntrySerDes
{
    /**
     * @var \Emphloyer\JobSerDes
     */
    protected $jobSerDes;

    /**
     * @param \Emphloyer\JobSerDes $jobSerDes
     */
    public function __construct(\Emphloyer\JobSerDes $jobSerDes)
    {
        $this->jobSerDes = $jobSerDes;
    }

    /**
     * @param array $attributes
     * @return \Emphloyer\Scheduler\ScheduleEntry
     */
    public function deserialize(array $attributes)
    {
        $job = $this->jobSerDes->deserialize($attributes["job"]);
        $entry = new \Emphloyer\Scheduler\ScheduleEntry($job, $attributes["id"], $attributes["minute"],
            $attributes["hour"], $attributes["dayOfMonth"], $attributes["month"], $attributes["dayOfWeek"]);
        return $entry;
    }
}
