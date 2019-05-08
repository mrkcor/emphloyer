<?php

declare(strict_types=1);

namespace Emphloyer\Scheduler;

use DateTime;
use Iterator;

/**
 * Implement this interface to build your own Scheduler backend.
 */
interface Backend
{
    /**
     * Reconnect the backend.
     */
    public function reconnect() : void;

    /**
     * Clear the entire schedule.
     */
    public function clear() : void;

    /**
     * List the entire schedule.
     */
    public function allEntries() : Iterator;

    /**
     * Find a specific entry in the schedule using its id and return its attributes.
     *
     * @param mixed $id
     *
     * @return mixed[]|null
     */
    public function find($id) : ?array;

    /**
     * Delete an entry from the schedule using its id
     *
     * @param mixed $id
     */
    public function delete($id) : void;

    /**
     * Schedule a job.
     *
     * @param mixed[]  $job        Job to schedule
     * @param int|null $minute     Minute to schedule on
     * @param int|null $hour       Hour to schedule on
     * @param int|null $dayOfMonth Day of the month to schedule on
     * @param int|null $month      Month to schedule on
     * @param int|null $dayOfWeek  Week day to schedule on
     *
     * @return mixed[] Attributes of scheduled entry
     */
    public function schedule(
        array $job,
        ?int $minute = null,
        ?int $hour = null,
        ?int $dayOfMonth = null,
        ?int $month = null,
        ?int $dayOfWeek = null
    ) : array;

    /**
     * Get jobs scheduled for the given DateTime.
     *
     * @param DateTime $dateTime Date and time to get jobs for
     * @param bool     $lock     Lock the jobs found for a minute (to prevent concurrently running schedulers from
     *                           picking them up)
     *
     * @return mixed[] Jobs to be run for given DateTime.
     */
    public function getJobsFor(DateTime $dateTime, bool $lock = true) : array;
}
