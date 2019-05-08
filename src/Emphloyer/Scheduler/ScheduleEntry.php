<?php

declare(strict_types=1);

namespace Emphloyer\Scheduler;

use Emphloyer\Job;

class ScheduleEntry
{
    /**
     * Scheduled job
     *
     * @var Job
     */
    protected $job;
    /**
     * Minute to schedule on
     *
     * @var int
     */
    protected $minute;
    /**
     * Hour to schedule on
     *
     * @var int
     */
    protected $hour;
    /**
     * Day of the month to schedule on
     *
     * @var int
     */
    protected $dayOfMonth;
    /**
     * Month to schedule on
     *
     * @var int
     */
    protected $month;
    /**
     * Week day to schedule on
     *
     * @var int
     */
    protected $dayOfWeek;

    /**
     * @param Job      $job        Job to schedule
     * @param mixed    $id         Schedule entry ID
     * @param int|null $minute     Minute to schedule on
     * @param int|null $hour       Hour to schedule on
     * @param int|null $dayOfMonth Day of the month to schedule on
     * @param int|null $month      Month to schedule on
     * @param int|null $dayOfWeek  Week day to schedule on
     */
    public function __construct(
        Job $job,
        $id = null,
        ?int $minute = null,
        ?int $hour = null,
        ?int $dayOfMonth = null,
        ?int $month = null,
        ?int $dayOfWeek = null
    ) {
        $this->job        = $job;
        $this->id         = $id;
        $this->minute     = $minute;
        $this->hour       = $hour;
        $this->dayOfMonth = $dayOfMonth;
        $this->month      = $month;
        $this->dayOfWeek  = $dayOfWeek;
    }

    public function getJob() : Job
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

    public function getMinute() : ?int
    {
        return $this->minute;
    }

    public function getHour() : ?int
    {
        return $this->hour;
    }

    public function getDayOfMonth() : ?int
    {
        return $this->dayOfMonth;
    }

    public function getMonth() : ?int
    {
        return $this->month;
    }

    public function getDayOfWeek() : ?int
    {
        return $this->dayOfWeek;
    }
}
