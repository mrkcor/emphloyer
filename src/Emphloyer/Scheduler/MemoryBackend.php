<?php

declare(strict_types=1);

namespace Emphloyer\Scheduler;

use ArrayIterator;
use DateTime;
use Iterator;

/**
 * MemoryBackend provides you with a backend for the Scheduler that works within PHP's memory.
 * This backend is of use as an example and to build your own scripts making use of Emphloyer to run tasks in a
 * specific schedule.
 */
class MemoryBackend implements Backend
{
    /** @var int */
    protected $nr = 0;
    /** @var mixed[] */
    protected $schedule = [];

    /** @inheritDoc */
    public function reconnect() : void
    {
    }

    /** @inheritDoc */
    public function clear() : void
    {
        $this->nr       = 0;
        $this->schedule = [];
    }

    /** @inheritDoc */
    public function allEntries() : Iterator
    {
        return new ArrayIterator($this->schedule);
    }

    /** @inheritDoc */
    public function find($id) : ?array
    {
        foreach ($this->schedule as $entry) {
            if ($entry['id'] === $id) {
                return $entry;
            }
        }

        return null;
    }

    /** @inheritDoc */
    public function delete($id) : void
    {
        foreach ($this->schedule as $idx => $entry) {
            if ($entry['id'] === $id) {
                unset($this->schedule[$idx]);
                break;
            }
        }
    }

    /** @inheritDoc */
    public function schedule(
        array $job,
        ?int $minute = null,
        ?int $hour = null,
        ?int $dayOfMonth = null,
        ?int $month = null,
        ?int $dayOfWeek = null
    ) : array {
        $this->nr        += 1;
        $id               = $this->nr;
        $job['id']        = $id;
        $entry            = [
            'id' => $id,
            'job' => $job,
            'minute' => $minute,
            'hour' => $hour,
            'dayOfMonth' => $dayOfMonth,
            'month' => $month,
            'dayOfWeek' => $dayOfWeek,
            'locked' => null,
        ];
        $this->schedule[] = $entry;

        return $entry;
    }

    /** @inheritDoc */
    public function getJobsFor(DateTime $dateTime, bool $lock = true) : array
    {
        $jobs = [];

        $minute     = (int) $dateTime->format('i');
        $hour       = (int) $dateTime->format('H');
        $dayOfMonth = (int) $dateTime->format('d');
        $month      = (int) $dateTime->format('m');
        $dayOfWeek  = (int) $dateTime->format('w');

        foreach ($this->schedule as $idx => $schedule) {
            if ($schedule['minute'] !== null && $schedule['minute'] !== $minute) {
                continue;
            }

            if ($schedule['hour'] !== null && $schedule['hour'] !== $hour) {
                continue;
            }

            if ($schedule['dayOfMonth'] !== null && $schedule['dayOfMonth'] !== $dayOfMonth) {
                continue;
            }

            if ($schedule['month'] !== null && $schedule['month'] !== $month) {
                continue;
            }

            if ($schedule['dayOfWeek'] !== null && $schedule['dayOfWeek'] !== $dayOfWeek) {
                continue;
            }

            if ($lock) {
                if ($schedule['locked'] !== null && $schedule['locked'] >= $dateTime) {
                    continue;
                }

                $this->schedule[$idx]['locked'] = $dateTime;
            }

            $jobs[] = $schedule['job'];
        }

        return $jobs;
    }
}
