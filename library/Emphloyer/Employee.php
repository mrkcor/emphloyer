<?php

namespace Emphloyer;

/**
 * These are Emphloyer's workers.
 */
class Employee {
  const BUSY = 1;
  const COMPLETE = 2;
  const FAILED = 3;

  protected $job;
  protected $workPid;
  protected $workState = self::COMPLETE;
  protected $forkHooks;

  /**
   * Instantiate a new Employee.
   * @param \Emphloyer\Job\ForkHookChain $forkHooks
   * @return \Emphloyer\Employee
   */
  public function __construct(Job\ForkHookChain $forkHooks = null) {
    if (is_null($forkHooks)) {
      $forkHooks = new Job\ForkHookChain();
    }
    $this->forkHooks = $forkHooks;
  }

  /**
   * Get the fork hook chain.
   * @return \Emphloyer\Job\ForkHookChain
   */
  public function getForkHooks() {
    return $this->forkHooks;
  }

  /**
   * Tell the Employee to work on the given job.
   * @param \Emphloyer\Job $job
   */
  public function work(Job $job) {
    if (!$this->isFree()) {
      throw new Exceptions\EmployeeNotFreeException();
    }

    $this->workPid = pcntl_fork();

    if ($this->workPid == -1) {
      throw new Exceptions\ForkFailedException();
    }

    $this->workState = self::BUSY;
    $this->job = $job;

    if ($this->workPid == 0) {
      try {
        $this->getForkHooks()->run($job);
        $job->perform();
        exit(0);
      } catch (\Exception $exception) {
        exit(1);
      }
    }
  }

  /**
   * Get the Employee's current job.
   * @return \Emphloyer\Job|null
   */
  public function getJob() {
    return $this->job;
  }

  /**
   * Check if the Employee is working.
   * @return bool
   */
  public function isBusy() {
    $this->getWorkState();
    return $this->workState == self::BUSY;
  }

  /**
   * Get the current work PID.
   * @return int|null
   */
  public function getWorkPid() {
    return $this->workPid;
  }

  /**
   * Check if the Employee has a job or not.
   * @return bool
   */
  public function isFree() {
    return is_null($this->job);
  }

  /**
   * Clear the employee's job.
   */
  public function free() {
    if ($this->isBusy()) {
      throw new Exceptions\EmployeeIsBusyException();
    }
    $this->job = null;
  }

  /**
   * Get the Employee's work state.
   * @param bool Wait for completion (defaults to false)
   * @return int
   */
  public function getWorkState($wait = false) {
    if (!is_null($this->workPid)) {
      if (!posix_kill($this->workPid, 0)) {
        $this->workState = self::FAILED;
        $this->workPid = null;
      } else {
        $status = null;

        if ($wait === true) {
          $args = null;
        } else {
          $args = \WNOHANG;
        }
        $exitPid = pcntl_waitpid($this->workPid, $status, $args);

        if ($exitPid == $this->workPid) {
          $this->workState = self::FAILED;
          if (pcntl_wifexited($status)) {
            if (pcntl_wexitstatus($status) == 0) {
              $this->workState = self::COMPLETE;
            }
          }
          $this->workPid = null;
        }
      }
    }

    return $this->workState;
  }

  /**
   * Stop the job immediately.
   */
  public function stop() {
    if ($this->isBusy()) {
      posix_kill($this->workPid, \SIGKILL);
      $this->getWorkState(true);
    }
  }
}
