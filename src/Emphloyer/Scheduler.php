<?php

declare(strict_types=1);

namespace Emphloyer;

use DateTime;
use Emphloyer\Scheduler\Backend;
use Emphloyer\Scheduler\ScheduleEntry;
use Emphloyer\Scheduler\ScheduleEntryIterator;
use Emphloyer\Scheduler\ScheduleEntrySerDes;

/**
 * A Scheduler holds scheduled jobs to be done. A backend that implements the
 * \Emphloyer\Scheduler\Backend interface must be provided to handle the
 * storage and retrieval of schedule data.
 */
class Scheduler
{
    /** @var Backend */
    protected $backend;
    /** @var JobSerDes */
    protected $jobSerDes;
    /** @var ScheduleEntrySerDes */
    protected $entrySerDes;

    /**
     * Instantiate a new scheduler.
     *
     * @param Backend $backend Scheduler backend.
     */
    public function __construct(Backend $backend)
    {
        $this->backend     = $backend;
        $this->jobSerDes   = new JobSerDes();
        $this->entrySerDes = new ScheduleEntrySerDes($this->jobSerDes);
    }

    /**
     * Reconnect the backend if required.
     */
    public function reconnect() : void
    {
        $this->backend->reconnect();
    }

    /**
     * Clear the entire schedule.
     */
    public function clear() : void
    {
        $this->backend->clear();
    }

    /**
     * Find a specific entry in the schedule
     *
     * @param mixed $id
     */
    public function find($id) : ?ScheduleEntry
    {
        $attributes = $this->backend->find($id);
        if ($attributes !== null) {
            return $this->deserializeEntry($attributes);
        }

        return null;
    }

    /**
     * Convert a schedule entry in array form to an object
     *
     * @param mixed[] $attributes
     */
    public function deserializeEntry(array $attributes) : ScheduleEntry
    {
        return $this->entrySerDes->deserialize($attributes);
    }

    /**
     * Delete a specific entry in the schedule
     *
     * @param mixed $id
     */
    public function delete($id) : void
    {
        $this->backend->delete($id);
    }

    /**
     * List the entire schedule.
     */
    public function allEntries() : ScheduleEntryIterator
    {
        return new ScheduleEntryIterator($this->backend->allEntries());
    }

    /**
     * Schedule a job.
     *
     * @param Job      $job        Job to schedule
     * @param int|null $minute     Minute to schedule on
     * @param int|null $hour       Hour to schedule on
     * @param int|null $dayOfMonth Day of the month to schedule on
     * @param int|null $month      Month to schedule on
     * @param int|null $dayOfWeek  Week day to schedule on
     *
     * @return ScheduleEntry Scheduled entry
     */
    public function schedule(
        Job $job,
        ?int $minute = null,
        ?int $hour = null,
        ?int $dayOfMonth = null,
        ?int $month = null,
        ?int $dayOfWeek = null
    ) : ScheduleEntry {
        $attributes = $this->backend->schedule(
            $this->serializeJob($job),
            $minute,
            $hour,
            $dayOfMonth,
            $month,
            $dayOfWeek
        );

        return $this->deserializeEntry($attributes);
    }

    /**
     * Convert a job into an array that can be passed on to a backend.
     *
     * @return mixed[]
     */
    protected function serializeJob(Job $job) : array
    {
        return $this->jobSerDes->serialize($job);
    }

    /**
     * Get jobs scheduled for the given DateTime.
     *
     * @param DateTime $dateTime Date and time to get jobs for
     * @param bool     $lock     Lock the jobs found for a minute (to prevent concurrently running schedulers from
     *                           picking them up)
     *
     * @return Job[] Jobs to be run for given DateTime.
     */
    public function getJobsFor(DateTime $dateTime, bool $lock = true) : array
    {
        $dateTime->setTime((int) $dateTime->format('H'), (int) $dateTime->format('i'), 0);
        $serializedJobs = $this->backend->getJobsFor($dateTime, $lock);
        $jobs           = [];
        foreach ($serializedJobs as $job) {
            $jobs[] = $this->deserializeJob($job);
        }

        return $jobs;
    }

    /**
     * Convert an array provided by a backend into a Job instance.
     *
     * @param mixed[] $attributes
     */
    protected function deserializeJob(array $attributes) : Job
    {
        return $this->jobSerDes->deserialize($attributes);
    }
}
