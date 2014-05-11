<?php

namespace Emphloyer;

/**
 * A Scheduler holds scheduled jobs to be done. A backend that implements the 
 * \Emphloyer\Scheduler\Backend interface must be provided to handle the 
 * storage and retrieval of schedule data.
 */
class Scheduler {
  /**
   * @var \Emphloyer\Scheduler\Backend
   */
  protected $backend;

  /**
   * Instantiate a new scheduler.
   * @param \Emphloyer\Scheduler\Backend $backend Scheduler backend.
   * @return \Emphloyer\Scheduler
   */
  public function __construct(\Emphloyer\Scheduler\Backend $backend) {
    $this->backend = $backend;
  }

  /**
   * Reconnect the backend if required.
   */
  public function reconnect() {
    $this->backend->reconnect();
  }

  /**
   * Clear the entire schedule.
   */
  public function clear() {
    $this->backend->clear();
  }

  /**
   * Schedule a job.
   * @param array $job Job to schedule
   * @param int $minute Minute to schedule on
   * @param int $hour Hour to schedule on
   * @param int $dayOfMonth Day of the month to schedule on
   * @param int $month Month to schedule on
   * @param int $dayOfWeek Week day to schedule on
   * @return \Emphloyer\Job Scheduled job
   */
  public function schedule(Job $job, $minute = null, $hour = null, $dayOfMonth = null, $month = null, $dayOfWeek = null) {
    $attributes = $this->backend->schedule($this->serializeJob($job), $minute, $hour, $dayOfMonth, $month, $dayOfWeek);
    return $this->deserializeJob($attributes);
  }

  /**
   * Get jobs scheduled for the given DateTime.
   * @param \DateTime $dateTime
   * @param boolean $lock Lock the jobs found for a minute (to prevent concurrently running schedulers from picking them up)
   * @return array Jobs to be run for given DateTime.
   */
  public function getJobsFor(\DateTime $dateTime, $lock = true) {
    $dateTime->setTime($dateTime->format("H"), $dateTime->format("i"), 0);
    $serializedJobs = $this->backend->getJobsFor($dateTime, $lock);
    $jobs = array();
    foreach ($serializedJobs as $job) {
      $jobs[] = $this->deserializeJob($job);
    }
    return $jobs;
  }

  /**
   * Convert a job into an array that can be passed on to a backend.
   * @param \Emphloyer\Job $job
   * @return array
   */
  protected function serializeJob(Job $job) {
    $attributes = $job->getAttributes();
    $attributes['className'] = get_class($job);
    return $attributes;
  }

  /**
   * Convert an array provided by a backend into a Job instance.
   * @param array $attributes
   * @return \Emphloyer\Job
   */
  protected function deserializeJob($attributes) {
    if (isset($attributes['className'])) {
      $className = $attributes['className'];
      $job = new $className();
      unset($attributes['className']);
      $job->setAttributes($attributes);
      return $job;
    }
  }
}
