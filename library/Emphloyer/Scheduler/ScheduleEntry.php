<?php

namespace Emphloyer\Scheduler;

class ScheduleEntry
{
    /**
     * Scheduled job
     * @var \Emphloyer\Job
     */
    protected $job;

    /**
     * Minute to schedule on
     * @var int
     */
    protected $minute;

    /**
     * Hour to schedule on
     * @var int
     */
    protected $hour;

    /**
     * Day of the month to schedule on
     * @var int
     */
    protected $dayOfMonth;

    /**
     * Month to schedule on
     * @var int
     */
    protected $month;

    /**
     * Week day to schedule on
     * @var int
     */
    protected $dayOfWeek;

    /**
     * @param \Emphloyer\Job $job Job to schedule
     * @param mixed $id Schedule entry ID
     * @param int $minute Minute to schedule on
     * @param int $hour Hour to schedule on
     * @param int $dayOfMonth Day of the month to schedule on
     * @param int $month Month to schedule on
     * @param int $dayOfWeek Week day to schedule on
     */
    public function __construct(
        \Emphloyer\Job $job,
        $id = null,
        $minute = null,
        $hour = null,
        $dayOfMonth = null,
        $month = null,
        $dayOfWeek = null
    ) {
        $this->job = $job;
        $this->id = $id;
        $this->minute = $minute;
        $this->hour = $hour;
        $this->dayOfMonth = $dayOfMonth;
        $this->month = $month;
        $this->dayOfWeek = $dayOfWeek;
    }

    /**
     * @return \Emphloyer\Job
     */
    public function getJob()
    {
        return $this->job;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getMinute()
    {
        return $this->minute;
    }

    /**
     * @return int
     */
    public function getHour()
    {
        return $this->hour;
    }

    /**
     * @return int
     */
    public function getDayOfMonth()
    {
        return $this->dayOfMonth;
    }

    /**
     * @return int
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * @return int
     */
    public function getDayOfWeek()
    {
        return $this->dayOfWeek;
    }
}
