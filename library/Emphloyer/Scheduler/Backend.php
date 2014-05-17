<?php

namespace Emphloyer\Scheduler;

/**
 * Implement this interface to build your own Scheduler backend.
 */
interface Backend {
  /**
   * Reconnect the backend.
   */
  public function reconnect();

  /**
   * Clear the entire schedule.
   */
  public function clear();

  /**
   * List the entire schedule.
   * @return \Iterator
   */
  public function allEntries();

  /**
   * Find a specific entry in the schedule using its id and return its attributes.
   * @param mixed $id
   * @return array|null
   */
  public function find($id);

  /**
   * Delete an entry from the schedule using its id
   * @param mixed $id
   */
  public function delete($id);

  /**
   * Schedule a job.
   * @param array $job Job to schedule
   * @param int $minute Minute to schedule on
   * @param int $hour Hour to schedule on
   * @param int $dayOfMonth Day of the month to schedule on
   * @param int $month Month to schedule on
   * @param int $dayOfWeek Week day to schedule on
   * @return array Attributes of scheduled entry
   */
  public function schedule(array $job, $minute = null, $hour = null, $dayOfMonth = null, $month = null, $dayOfWeek = null);

  /**
   * Get jobs scheduled for the given DateTime.
   * @param \DateTime $dateTime
   * @param boolean $lock Lock the jobs found for a minute (to prevent concurrently running schedulers from picking them up)
   * @return array Jobs to be run for given DateTime.
   */
  public function getJobsFor(\DateTime $dateTime, $lock = true);
}
