<?php

namespace Emphloyer\Scheduler;

class BackendTestJob extends \Emphloyer\AbstractJob {
  public function setName($name) {
    $this->attributes['name'] = $name;
  }

  public function getName() {
    return $this->attributes['name'];
  }

  public function perform() {
  }
}

class BackendTestCase extends \PHPUnit_Framework_TestCase {
  public function setUp() {
    $this->scheduler = new \Emphloyer\Scheduler($this->backend);
    $this->scheduler->clear();
  }

  public function testSchedulingVariations() {
    $schedules = array(
      array(null, null, null, null, null), // every minute 
      array(5, null, null, null, null), // 5 minutes past every hour
      array(5, 2, null, null, null), // 2:05 every day
      array(5, 2, 1, null, null), // 2:05 on the first day of every month
      array(5, 2, 1, 1, null), // 2:05 on the first day of every january
      array(5, 2, null, null, 6), // 2:05 of every saturday
      array(null, 6, null, null, null), // every minute while its 6 o'clock
      array(null, null, null, null, 5), // every minute of every friday
    );

    $jobs = array();

    foreach ($schedules as $idx => $schedule) {
      $job = new BackendTestJob();
      $job->setName('Job ' . $idx);
      $jobs[$idx] = $this->scheduler->schedule($job, $schedule[0], $schedule[1], $schedule[2], $schedule[3], $schedule[4]);
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

            if ($hour == 2 && $minute == 5 && $month == 1 && $day == 1) {
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

  public function testClear() {
      $job = new BackendTestJob();
      $job->setName('Job');
      $scheduledJob = $this->scheduler->schedule($job);

      $dateTime = new \DateTime();
      $this->assertEquals(array($scheduledJob), $this->scheduler->getJobsFor($dateTime, false));

      $this->scheduler->clear();
      $this->assertEquals(array(), $this->scheduler->getJobsFor($dateTime, false));
  }

  public function testLockingScheduleEntries() {
    $schedules = array(
      array(null, null, null, null, null), // every minute 
      array(5, null, null, null, null), // 5 minutes past every hour
    );

    $jobs = array();

    foreach ($schedules as $idx => $schedule) {
      $job = new BackendTestJob();
      $job->setName('Job ' . $idx);
      $jobs[$idx] = $this->scheduler->schedule($job, $schedule[0], $schedule[1], $schedule[2], $schedule[3], $schedule[4]);
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
