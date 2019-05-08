<?php

declare(strict_types=1);

namespace Emphloyer;

use DateTime;
use function method_exists;
use function usleep;

/**
 * The Boss class is responsible for processing the work from a Pipeline with a pool of Employee instances.
 */
class Boss
{
    /** @var Pipeline */
    protected $pipeline;
    /** @var Scheduler|null */
    protected $scheduler;
    /** @var Employee[] */
    protected $employees = [];

    public function __construct(Pipeline $pipeline, ?Scheduler $scheduler = null)
    {
        $this->pipeline  = $pipeline;
        $this->scheduler = $scheduler;
    }

    /**
     * Allocate an employee
     */
    public function allocateEmployee(Employee $employee) : void
    {
        $this->employees[] = $employee;
    }

    /**
     * Check the schedule for work to enqueue and push it into the pipeline if needed
     */
    public function scheduleWork() : void
    {
        if ($this->scheduler === null) {
            return;
        }

        $this->scheduler->reconnect();

        foreach ($this->scheduler->getJobsFor(new DateTime()) as $job) {
            $this->pipeline->enqueue($job);
        }
    }

    /**
     * Cycle through all available employees and allocate work when it is available.
     */
    public function delegateWork() : void
    {
        foreach ($this->employees as $employee) {
            if (! $employee->isFree()) {
                continue;
            }

            $job = $this->getWork($employee);
            if (! $job) {
                continue;
            }

            $employee->work($job);
            $this->pipeline->reconnect();
        }
    }

    /**
     * Obtain a job from the pipeline for the given Employee.
     */
    public function getWork(Employee $employee) : ?Job
    {
        $job = $this->pipeline->dequeue($employee->getOptions());
        if ($job === null) {
            usleep(10000);
        }

        return $job;
    }

    /**
     * Wait for all busy employees to finish.
     */
    public function waitOnEmployees() : void
    {
        foreach ($this->employees as $employee) {
            if (! $employee->isBusy()) {
                continue;
            }

            $employee->getWorkState(true);
        }
    }

    /**
     * Stop all busy employees.
     */
    public function stopEmployees() : void
    {
        foreach ($this->employees as $employee) {
            if (! $employee->isBusy()) {
                continue;
            }

            $employee->stop();
        }
    }

    /**
     * Get a progress update from all employees.
     */
    public function updateProgress() : void
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
     *
     * @return Employee[]
     */
    public function getEmployees() : array
    {
        return $this->employees;
    }
}
