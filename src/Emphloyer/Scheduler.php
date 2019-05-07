<?php

namespace Emphloyer;

/**
 * A Scheduler holds scheduled jobs to be done. A backend that implements the
 * \Emphloyer\Scheduler\Backend interface must be provided to handle the
 * storage and retrieval of schedule data.
 */
class Scheduler
{
    /**
     * @var \Emphloyer\Scheduler\Backend
     */
    protected $backend;

    /**
     * @var \Emphloyer\JobSerDes
     */
    protected $jobSerDes;

    /**
     * @var \Emphloyer\Scheduler\ScheduleEntrySerDes
     */
    protected $entrySerDes;

    /**
     * Instantiate a new scheduler.
     * @param \Emphloyer\Scheduler\Backend $backend Scheduler backend.
     * @return \Emphloyer\Scheduler
     */
    public function __construct(\Emphloyer\Scheduler\Backend $backend)
    {
        $this->backend = $backend;
        $this->jobSerDes = new \Emphloyer\JobSerDes();
        $this->entrySerDes = new \Emphloyer\Scheduler\ScheduleEntrySerDes($this->jobSerDes);
    }

    /**
     * Reconnect the backend if required.
     */
    public function reconnect()
    {
        $this->backend->reconnect();
    }

    /**
     * Clear the entire schedule.
     */
    public function clear()
    {
        $this->backend->clear();
    }

    /**
     * Find a specific entry in the schedule
     * @param mixed $id
     */
    public function find($id)
    {
        $attributes = $this->backend->find($id);
        if (!is_null($attributes)) {
            return $this->deserializeEntry($attributes);
        }
    }

    /**
     * Convert a schedule entry in array form to an object
     * @param array $attributes
     * @return \Emphloyer\Scheduler\ScheduleEntry
     */
    public function deserializeEntry(array $attributes)
    {
        return $this->entrySerDes->deserialize($attributes);
    }

    /**
     * Delete a specific entry in the schedule
     * @param mixed $id
     */
    public function delete($id)
    {
        $this->backend->delete($id);
    }

    /**
     * List the entire schedule.
     * @return \Emphloyer\Scheduler\ScheduleEntryIterator
     */
    public function allEntries()
    {
        return new \Emphloyer\Scheduler\ScheduleEntryIterator($this->backend->allEntries());
    }

    /**
     * Schedule a job.
     * @param array $job Job to schedule
     * @param int $minute Minute to schedule on
     * @param int $hour Hour to schedule on
     * @param int $dayOfMonth Day of the month to schedule on
     * @param int $month Month to schedule on
     * @param int $dayOfWeek Week day to schedule on
     * @return \Emphloyer\Scheduler\ScheduleEntry Scheduled entry
     */
    public function schedule(
        Job $job,
        $minute = null,
        $hour = null,
        $dayOfMonth = null,
        $month = null,
        $dayOfWeek = null
    ) {
        $attributes = $this->backend->schedule($this->serializeJob($job), $minute, $hour, $dayOfMonth, $month,
            $dayOfWeek);
        return $this->deserializeEntry($attributes);
    }

    /**
     * Convert a job into an array that can be passed on to a backend.
     * @param \Emphloyer\Job $job
     * @return array
     */
    protected function serializeJob(Job $job)
    {
        return $this->jobSerDes->serialize($job);
    }

    /**
     * Get jobs scheduled for the given DateTime.
     * @param \DateTime $dateTime
     * @param boolean $lock Lock the jobs found for a minute (to prevent concurrently running schedulers from picking them up)
     * @return array Jobs to be run for given DateTime.
     */
    public function getJobsFor(\DateTime $dateTime, $lock = true)
    {
        $dateTime->setTime($dateTime->format("H"), $dateTime->format("i"), 0);
        $serializedJobs = $this->backend->getJobsFor($dateTime, $lock);
        $jobs = array();
        foreach ($serializedJobs as $job) {
            $jobs[] = $this->deserializeJob($job);
        }
        return $jobs;
    }

    /**
     * Convert an array provided by a backend into a Job instance.
     * @param array $attributes
     * @return \Emphloyer\Job
     */
    protected function deserializeJob($attributes)
    {
        return $this->jobSerDes->deserialize($attributes);
    }
}
