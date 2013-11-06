<?php

namespace Emphloyer;

/**
 * The Boss class is responsible for processing the work from a Pipeline with a pool of Employee instances.
 */
class Boss {
  protected $pipeline;
  protected $employees = array();

  /**
   * @param \Emphloyer\Pipeline $pipeline
   * @param int $numberOfEmployees
   * @return \Emphloyer\Boss
   */
  public function __construct(Pipeline $pipeline) {
    $this->pipeline = $pipeline;
  }

  /**
   * Allocate an employee
   * @param \Emphloyer\Employee
   */
  public function allocateEmployee(Employee $employee) {
    $this->employees[] = $employee;
  }

  /**
   * Allocate the next job from the pipeline to the first available employee.
   */
  public function delegateWork() {
    if ($this->hasAvailableEmployee() && $job = $this->getWork()) {
      $this->delegateJob($job);
    }
  }

  /**
   * Wait for all busy employees to finish.
   */
  public function waitOnEmployees() {
    foreach ($this->employees as $employee) {
      if ($employee->isBusy()) {
        $employee->getWorkState(true);
      }
    }
  }

  /**
   * Stop all busy employees.
   */
  public function stopEmployees() {
    foreach ($this->employees as $employee) {
      if ($employee->isBusy()) {
        $employee->stop();
      }
    }
  }

  /**
   * Get a progress update from all employees.
   */
  public function updateProgress() {
    foreach ($this->employees as $employee) {
      if ($employee->isBusy()) {
        continue;
      }

      if ($employee->isFree()) {
        continue;
      }

      $job = $employee->getJob();
      
      switch ($employee->getWorkState()) {
      case Employee::COMPLETE:
        $this->pipeline->complete($job);
        break;
      case Employee::FAILED:
        if ($job->mayTryAgain()) {
          $this->pipeline->reset($job);
        } else {
          $this->pipeline->fail($job);
        }
        break;
      }

      $employee->free();
    }
  }

  /**
   * Get the allocated employees
   * @return array
   */
  public function getEmployees() {
    return $this->employees;
  }

  /**
   * Check if there is atleast one free employee.
   * @return bool
   */
  public function hasAvailableEmployee() {
    foreach ($this->employees as $employee) {
      if ($employee->isFree()) {
        return true;
      }
    }
    return false;
  }

  /**
   * Obtain a job from the pipeline.
   * @return \Emphloyer\Job|null
   */
  public function getWork() {
    $job = $this->pipeline->dequeue();

    if (is_null($job)) {
      usleep(10000);
    }

    return $job;
  }

  /**
   * Allocate a job to an available employee.
   * @param \Emphloyer\Job $job
   */
  public function delegateJob(Job $job) {
    foreach ($this->employees as $employee) {
      if ($employee->isFree()) {
        $employee->work($job);
        $this->pipeline->reconnect();
        break;
      }
    }
  }
}
