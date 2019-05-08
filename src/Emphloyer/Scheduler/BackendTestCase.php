<?php

namespace Emphloyer\Scheduler;

class BackendTestJob extends \Emphloyer\AbstractJob
{
    public function setName($name)
    {
        $this->attributes['name'] = $name;
    }

    public function getName()
    {
        return $this->attributes['name'];
    }

    public function perform() : void
    {
    }
}

class BackendTestCase extends \PHPUnit\Framework\TestCase
{
    public function setUp() : void
    {
        $this->scheduler = new \Emphloyer\Scheduler($this->backend);
        $this->scheduler->clear();
    }

    public function testGetAllEntries()
    {
        $schedules = array(
            array(null, null, null, null, null), // every minute
            array(5, 2, null, null, null), // 2:05 every day
            array(5, 2, 1, 4, null), // 2:05 on the first day of every april
            array(5, 2, null, null, 6), // 2:05 of every saturday
        );

        $scheduled = array();

        foreach ($schedules as $idx => $schedule) {
            $job = new BackendTestJob();
            $job->setName('Job ' . $idx);
            $scheduled[] = $this->scheduler->schedule($job, $schedule[0], $schedule[1], $schedule[2], $schedule[3],
                $schedule[4]);
        }

        $entries = $this->scheduler->allEntries();
        $this->assertInstanceOf("Emphloyer\Scheduler\ScheduleEntryIterator", $entries);
        $entries = iterator_to_array($entries);
        $this->assertEquals(4, count($entries));

        $entry = $entries[0];
        $this->assertInstanceOf("Emphloyer\Scheduler\ScheduleEntry", $entry);
        $this->assertEquals($scheduled[0]->getId(), $entry->getId());
        $this->assertEquals("Job 0", $entry->getJob()->getName());
        $this->assertNull($entry->getMinute());
        $this->assertNull($entry->getHour());
        $this->assertNull($entry->getDayOfMonth());
        $this->assertNull($entry->getMonth());
        $this->assertNull($entry->getDayOfWeek());

        $entry = $entries[1];
        $this->assertInstanceOf("Emphloyer\Scheduler\ScheduleEntry", $entry);
        $this->assertEquals($scheduled[1]->getId(), $entry->getId());
        $this->assertEquals("Job 1", $entry->getJob()->getName());
        $this->assertEquals(5, $entry->getMinute());
        $this->assertEquals(2, $entry->getHour());
        $this->assertNull($entry->getDayOfMonth());
        $this->assertNull($entry->getMonth());
        $this->assertNull($entry->getDayOfWeek());

        $entry = $entries[2];
        $this->assertInstanceOf("Emphloyer\Scheduler\ScheduleEntry", $entry);
        $this->assertEquals($scheduled[2]->getId(), $entry->getId());
        $this->assertEquals("Job 2", $entry->getJob()->getName());
        $this->assertEquals(5, $entry->getMinute());
        $this->assertEquals(2, $entry->getHour());
        $this->assertEquals(1, $entry->getDayOfMonth());
        $this->assertEquals(4, $entry->getMonth());
        $this->assertNull($entry->getDayOfWeek());

        $entry = $entries[3];
        $this->assertInstanceOf("Emphloyer\Scheduler\ScheduleEntry", $entry);
        $this->assertEquals($scheduled[3]->getId(), $entry->getId());
        $this->assertEquals("Job 3", $entry->getJob()->getName());
        $this->assertEquals(5, $entry->getMinute());
        $this->assertEquals(2, $entry->getHour());
        $this->assertNull($entry->getDayOfMonth());
        $this->assertNull($entry->getMonth());
        $this->assertEquals(6, $entry->getDayOfWeek());
    }

    public function testFind()
    {
        $schedules = array(
            array(5, 2, 1, 4, null), // 2:05 on the first day of every april
            array(5, 2, null, null, 6), // 2:05 of every saturday
        );

        $scheduled = array();

        foreach ($schedules as $idx => $schedule) {
            $job = new BackendTestJob();
            $job->setName('Job ' . $idx);
            $scheduled[] = $this->scheduler->schedule($job, $schedule[0], $schedule[1], $schedule[2], $schedule[3],
                $schedule[4]);
        }

        $entry = $this->scheduler->find($scheduled[1]->getId());
        $this->assertInstanceOf("Emphloyer\Scheduler\ScheduleEntry", $entry);
        $this->assertEquals($scheduled[1]->getId(), $entry->getId());
        $this->assertEquals("Job 1", $entry->getJob()->getName());
        $this->assertEquals(5, $entry->getMinute());
        $this->assertEquals(2, $entry->getHour());
        $this->assertNull($entry->getDayOfMonth());
        $this->assertNull($entry->getMonth());
        $this->assertEquals(6, $entry->getDayOfWeek());

        $entry = $this->scheduler->find($scheduled[0]->getId());
        $this->assertInstanceOf("Emphloyer\Scheduler\ScheduleEntry", $entry);
        $this->assertEquals($scheduled[0]->getId(), $entry->getId());
        $this->assertEquals("Job 0", $entry->getJob()->getName());
        $this->assertEquals(5, $entry->getMinute());
        $this->assertEquals(2, $entry->getHour());
        $this->assertEquals(1, $entry->getDayOfMonth());
        $this->assertEquals(4, $entry->getMonth());
        $this->assertNull($entry->getDayOfWeek());

        $this->assertNull($this->scheduler->find('NO SUCH ID'));
    }

    public function testDelete()
    {
        $schedules = array(
            array(5, 2, 1, 4, null), // 2:05 on the first day of every april
            array(5, 2, null, null, 6), // 2:05 of every saturday
        );

        $scheduled = array();

        foreach ($schedules as $idx => $schedule) {
            $job = new BackendTestJob();
            $job->setName('Job ' . $idx);
            $scheduled[] = $this->scheduler->schedule($job, $schedule[0], $schedule[1], $schedule[2], $schedule[3],
                $schedule[4]);
        }

        $entry = $this->scheduler->find($scheduled[1]->getId());
        $this->assertInstanceOf("Emphloyer\Scheduler\ScheduleEntry", $entry);
        $this->assertEquals($scheduled[1]->getId(), $entry->getId());

        $entry = $this->scheduler->find($scheduled[0]->getId());
        $this->assertInstanceOf("Emphloyer\Scheduler\ScheduleEntry", $entry);
        $this->assertEquals($scheduled[0]->getId(), $entry->getId());

        $this->scheduler->delete($scheduled[1]->getId());
        $this->assertNull($this->scheduler->find($scheduled[1]->getId()));
        $this->assertInstanceOf("Emphloyer\Scheduler\ScheduleEntry", $this->scheduler->find($scheduled[0]->getId()));

        $this->scheduler->delete($scheduled[0]->getId());
        $this->assertNull($this->scheduler->find($scheduled[1]->getId()));
        $this->assertNull($this->scheduler->find($scheduled[0]->getId()));
    }

    public function testSchedulingVariations()
    {
        $schedules = array(
            array(null, null, null, null, null), // every minute
            array(5, null, null, null, null), // 5 minutes past every hour
            array(5, 2, null, null, null), // 2:05 every day
            array(5, 2, 1, null, null), // 2:05 on the first day of every month
            array(5, 2, 1, 3, null), // 2:05 on the first day of every march
            array(5, 2, null, null, 6), // 2:05 of every saturday
            array(null, 6, null, null, null), // every minute while its 6 o'clock
            array(null, null, null, null, 5), // every minute of every friday
        );

        $jobs = array();

        foreach ($schedules as $idx => $schedule) {
            $job = new BackendTestJob();
            $job->setName('Job ' . $idx);
            $entry = $this->scheduler->schedule($job, $schedule[0], $schedule[1], $schedule[2], $schedule[3],
                $schedule[4]);
            $this->assertInstanceOf("Emphloyer\Scheduler\ScheduleEntry", $entry);
            $this->assertInstanceOf("Emphloyer\Scheduler\BackendTestJob", $entry->getJob());
            $this->assertEquals('Job ' . $idx, $entry->getJob()->getName());
            $this->assertEquals($schedule[0], $entry->getMinute());
            $this->assertEquals($schedule[1], $entry->getHour());
            $this->assertEquals($schedule[2], $entry->getDayOfMonth());
            $this->assertEquals($schedule[3], $entry->getMonth());
            $this->assertEquals($schedule[4], $entry->getDayOfWeek());
            $jobs[$idx] = $entry->getJob();
        }

        $maxDays = array(
            1 => 31,
            2 => 28,
            3 => 31,
            4 => 30,
            5 => 31,
            6 => 30,
            7 => 31,
            8 => 31,
            9 => 30,
            10 => 31,
            11 => 30,
            12 => 31,
        );

        // for each month
        for ($month = 1; $month < 13; $month++) {
            // for each day
            for ($day = 1; $day < ($maxDays[$month] + 1); $day++) {
                // for each hour
                for ($hour = 0; $hour < 24; $hour++) {
                    // for each minute
                    for ($minute = 0; $minute < 60; $minute++) {
                        $dateTime = new \DateTime("2014-$month-$day $hour:$minute", new \DateTimeZone("UTC"));
                        $dayOfWeek = $dateTime->format("w");

                        $jobs = $this->scheduler->getJobsFor($dateTime);

                        $jobNames = array();
                        foreach ($jobs as $job) {
                            $jobNames[] = $job->getName();
                        }

                        $expectedJobNames = array();
                        $expectedJobNames[] = "Job 0";

                        if ($minute == 5) {
                            $expectedJobNames[] = "Job 1";
                        }

                        if ($hour == 2 && $minute == 5) {
                            $expectedJobNames[] = "Job 2";
                        }

                        if ($hour == 2 && $minute == 5 && $day == 1) {
                            $expectedJobNames[] = "Job 3";
                        }

                        if ($hour == 2 && $minute == 5 && $month == 3 && $day == 1) {
                            $expectedJobNames[] = "Job 4";
                        }

                        if ($dayOfWeek == 6 && $hour == 2 && $minute == 5) {
                            $expectedJobNames[] = "Job 5";
                        }

                        if ($hour == 6) {
                            $expectedJobNames[] = "Job 6";
                        }

                        if ($dayOfWeek == 5) {
                            $expectedJobNames[] = "Job 7";
                        }

                        $this->assertEquals($expectedJobNames, $jobNames);
                    }
                }
            }
        }
    }

    public function testClear()
    {
        $job = new BackendTestJob();
        $job->setName('Job');
        $this->scheduler->schedule($job);

        $entries = $this->scheduler->allEntries();
        $this->assertEquals(1, count(iterator_to_array($entries)));

        $this->scheduler->clear();
        $entries = $this->scheduler->allEntries();
        $this->assertEquals(0, count(iterator_to_array($entries)));
    }

    public function testLockingScheduleEntries()
    {
        $schedules = array(
            array(null, null, null, null, null), // every minute
            array(5, null, null, null, null), // 5 minutes past every hour
        );

        $jobs = array();

        foreach ($schedules as $idx => $schedule) {
            $job = new BackendTestJob();
            $job->setName('Job ' . $idx);
            $entry = $this->scheduler->schedule($job, $schedule[0], $schedule[1], $schedule[2], $schedule[3],
                $schedule[4]);
            $this->assertInstanceOf("Emphloyer\Scheduler\ScheduleEntry", $entry);
            $this->assertInstanceOf("Emphloyer\Scheduler\BackendTestJob", $entry->getJob());
            $this->assertEquals('Job ' . $idx, $entry->getJob()->getName());
            $this->assertEquals($schedule[0], $entry->getMinute());
            $this->assertEquals($schedule[1], $entry->getHour());
            $this->assertEquals($schedule[2], $entry->getDayOfMonth());
            $this->assertEquals($schedule[3], $entry->getMonth());
            $this->assertEquals($schedule[4], $entry->getDayOfWeek());
            $jobs[$idx] = $entry->getJob();
        }

        $dateTime = new \DateTime("2014-05-11 13:05", new \DateTimeZone("UTC"));
        $this->assertEquals(array($jobs[0], $jobs[1]), $this->scheduler->getJobsFor($dateTime));
        $this->assertEquals(array(), $this->scheduler->getJobsFor($dateTime));
        $this->assertEquals(array($jobs[0], $jobs[1]), $this->scheduler->getJobsFor($dateTime, false));

        $dateTime = new \DateTime("2014-05-11 13:06", new \DateTimeZone("UTC"));
        $this->assertEquals(array($jobs[0]), $this->scheduler->getJobsFor($dateTime));
        $this->assertEquals(array(), $this->scheduler->getJobsFor($dateTime));
        $this->assertEquals(array($jobs[0]), $this->scheduler->getJobsFor($dateTime, false));

        $dateTime = new \DateTime("2014-05-11 13:05", new \DateTimeZone("UTC"));
        $this->assertEquals(array(), $this->scheduler->getJobsFor($dateTime));

        $dateTime = new \DateTime("2014-05-11 14:05", new \DateTimeZone("UTC"));
        $this->assertEquals(array($jobs[0], $jobs[1]), $this->scheduler->getJobsFor($dateTime));
    }
}
