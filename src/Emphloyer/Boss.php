<?php

namespace Emphloyer;

/**
 * The Boss class is responsible for processing the work from a Pipeline with a pool of Employee instances.
 */
class Boss
{
    protected $pipeline;
    protected $scheduler;
    protected $employees = array();

    /**
     * @param \Emphloyer\Pipeline $pipeline
     * @param \Emphloyer\Scheduler|null $scheduler
     * @return \Emphloyer\Boss
     */
    public function __construct(Pipeline $pipeline, Scheduler $scheduler = null)
    {
        $this->pipeline = $pipeline;
        $this->scheduler = $scheduler;
    }

    /**
     * Allocate an employee
     * @param \Emphloyer\Employee
     */
    public function allocateEmployee(Employee $employee)
    {
        $this->employees[] = $employee;
    }

    /**
     * Check the schedule for work to enqueue and push it into the pipeline if needed
     */
    public function scheduleWork()
    {
        if (is_null($this->scheduler)) {
            return;
        }

        $this->scheduler->reconnect();

        foreach ($this->scheduler->getJobsFor(new \DateTime()) as $job) {
            $this->pipeline->enqueue($job);
        }
    }

    /**
     * Cycle through all available employees and allocate work when it is available.
     */
    public function delegateWork()
    {
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
     * Obtain a job from the pipeline for the given Employee.
     * @return \Emphloyer\Job|null
     */
    public function getWork(Employee $employee)
    {
        $job = $this->pipeline->dequeue($employee->getOptions());

        if (is_null($job)) {
            usleep(10000);
        }

        return $job;
    }

    /**
     * Wait for all busy employees to finish.
     */
    public function waitOnEmployees()
    {
        foreach ($this->employees as $employee) {
            if ($employee->isBusy()) {
                $employee->getWorkState(true);
            }
        }
    }

    /**
     * Stop all busy employees.
     */
    public function stopEmployees()
    {
        foreach ($this->employees as $employee) {
            if ($employee->isBusy()) {
                $employee->stop();
            }
        }
    }

    /**
     * Get a progress update from all employees.
     */
    public function updateProgress()
    {
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
                    if (method_exists($job, 'beforeComplete')) {
                        $job->beforeComplete();
                    }
                    $this->pipeline->complete($job);
                    break;
                case Employee::FAILED:
                    if (method_exists($job, 'beforeFail')) {
                        $job->beforeFail();
                    }
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
    public function getEmployees()
    {
        return $this->employees;
    }
}
