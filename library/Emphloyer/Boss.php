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
   * Cycle through all available employees and allocate work when it is available.
   */
  public function delegateWork() {
    foreach ($this->employees as $employee) {
      if ($employee->isFree()) {
        if ($job = $this->getWork($employee)) {
          $employee->work($job);
          $this->pipeline->reconnect();
        }
      }
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
   * Obtain a job from the pipeline for the given Employee.
   * @return \Emphloyer\Job|null
   */
  public function getWork(Employee $employee) {
    $job = $this->pipeline->dequeue($employee->getOptions());

    if (is_null($job)) {
      usleep(10000);
    }

    return $job;
  }
}
