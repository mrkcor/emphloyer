<?php

namespace Emphloyer\Scheduler;

/**
 * MemoryBackend provides you with a backend for the Scheduler that works within PHP's memory.
 * This backend is of use as an example and to build your own scripts making use of Emphloyer to run tasks in a specific schedule.
 */
class MemoryBackend implements Backend
{
    protected $nr = 0;
    protected $schedule = array();

    /**
     * Reconnect the backend.
     */
    public function reconnect()
    {
    }

    public function clear()
    {
        $this->nr = 0;
        $this->schedule = array();
    }

    public function allEntries()
    {
        return new \ArrayIterator($this->schedule);
    }

    public function find($id)
    {
        foreach ($this->schedule as $entry) {
            if ($entry["id"] == $id) {
                return $entry;
            }
        }
    }

    public function delete($id)
    {
        foreach ($this->schedule as $idx => $entry) {
            if ($entry["id"] == $id) {
                unset($this->schedule[$idx]);
                break;
            }
        }
    }

    public function schedule(
        array $job,
        $minute = null,
        $hour = null,
        $dayOfMonth = null,
        $month = null,
        $dayOfWeek = null
    ) {
        $this->nr += 1;
        $id = $this->nr;
        $job['id'] = $id;
        $entry = array(
            "id" => $id,
            "job" => $job,
            "minute" => $minute,
            "hour" => $hour,
            "dayOfMonth" => $dayOfMonth,
            "month" => $month,
            "dayOfWeek" => $dayOfWeek,
            "locked" => null
        );
        $this->schedule[] = $entry;
        return $entry;
    }

    public function getJobsFor(\DateTime $dateTime, $lock = true)
    {
        $jobs = array();

        $minute = $dateTime->format("i");
        $hour = $dateTime->format("H");
        $dayOfMonth = $dateTime->format("d");
        $month = $dateTime->format("m");
        $dayOfWeek = $dateTime->format("w");

        foreach ($this->schedule as $idx => $schedule) {
            if (!is_null($schedule["minute"]) && $schedule["minute"] != $minute) {
                continue;
            }

            if (!is_null($schedule["hour"]) && $schedule["hour"] != $hour) {
                continue;
            }

            if (!is_null($schedule["dayOfMonth"]) && $schedule["dayOfMonth"] != $dayOfMonth) {
                continue;
            }

            if (!is_null($schedule["month"]) && $schedule["month"] != $month) {
                continue;
            }

            if (!is_null($schedule["dayOfWeek"]) && $schedule["dayOfWeek"] != $dayOfWeek) {
                continue;
            }

            if ($lock) {
                if (is_null($schedule["locked"]) || $schedule["locked"] < $dateTime) {
                    $this->schedule[$idx]["locked"] = $dateTime;
                } else {
                    continue;
                }
            }

            $jobs[] = $schedule["job"];
        }

        return $jobs;
    }
}
