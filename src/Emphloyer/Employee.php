<?php

declare(strict_types=1);

namespace Emphloyer;

use InvalidArgumentException;
use Throwable;
use const SIGKILL;
use const WNOHANG;
use function get_class;
use function is_array;
use function pcntl_fork;
use function pcntl_waitpid;
use function pcntl_wexitstatus;
use function pcntl_wifexited;
use function posix_kill;

/**
 * These are Emphloyer's workers.
 */
class Employee
{
    public const BUSY     = 1;
    public const COMPLETE = 2;
    public const FAILED   = 3;
    /** @var mixed[] */
    protected $options;
    /** @var Job */
    protected $job;
    /** @var int */
    protected $workPid;
    /** @var int */
    protected $workState = self::COMPLETE;

    /**
     * Instantiate a new Employee.
     *
     * @param mixed[] $options Options that influence the behavior of the employee.
     */
    public function __construct(array $options = [])
    {
        if (isset($options['only']) && ! is_array($options['only'])) {
            throw new InvalidArgumentException('The only option must be an array');
        }
        if (isset($options['exclude']) && ! is_array($options['exclude'])) {
            throw new InvalidArgumentException('The exclude option must be an array');
        }
        $this->options = $options;
    }

    /**
     * Get the Employee options.
     *
     * @return mixed[]
     */
    public function getOptions() : array
    {
        return $this->options;
    }

    /**
     * Tell the Employee to work on the given job.
     */
    public function work(Job $job) : void
    {
        if (! $this->isFree()) {
            throw new Exceptions\EmployeeNotFree();
        }

        $this->workPid = pcntl_fork();

        if ($this->workPid === -1) {
            throw new Exceptions\ForkFailed();
        }

        $this->workState = self::BUSY;
        $this->job       = $job;

        if ($this->workPid !== 0) {
            return;
        }

        try {
            $job->perform();
            exit(0);
        } catch (Throwable $exception) {
            Logger::getLogger()->error(
                'Uncaught exception in Emphloyer Job.',
                [
                    'job_class' => get_class($job),
                    'job_id' => $job->getId(),
                    'exception_code' => $exception->getCode(),
                    'exception_message' => $exception->getMessage(),
                    'exception_stacktrace' => $exception->getTraceAsString(),
                    'exception' => $exception,
                ]
            );
            exit(1);
        }
    }

    /**
     * Check if the Employee has a job or not.
     */
    public function isFree() : bool
    {
        return $this->job === null;
    }

    /**
     * Get the Employee's current job.
     */
    public function getJob() : ?Job
    {
        return $this->job;
    }

    /**
     * Get the current work PID.
     */
    public function getWorkPid() : ?int
    {
        return $this->workPid;
    }

    /**
     * Clear the employee's job.
     */
    public function free() : void
    {
        if ($this->isBusy()) {
            throw new Exceptions\EmployeeIsBusy();
        }
        $this->job = null;
    }

    /**
     * Check if the Employee is working.
     */
    public function isBusy() : bool
    {
        $this->getWorkState();

        return $this->workState === self::BUSY;
    }

    /**
     * Get the Employee's work state.
     *
     * @param bool $wait Wait for completion (defaults to false)
     */
    public function getWorkState(bool $wait = false) : int
    {
        if ($this->workPid === null) {
            return $this->workState;
        }

        if (! posix_kill($this->workPid, 0)) {
            $this->workState = self::FAILED;
            $this->workPid   = null;

            return $this->workState;
        }

        $status = null;
        $args   = $wait ? 0 : WNOHANG;

        $exitPid = pcntl_waitpid($this->workPid, $status, $args);

        if ($exitPid === $this->workPid) {
            $this->workState = self::FAILED;
            if (pcntl_wifexited($status)) {
                if (pcntl_wexitstatus($status) === 0) {
                    $this->workState = self::COMPLETE;
                }
            }
            $this->workPid = null;
        }

        return $this->workState;
    }

    /**
     * Stop the job immediately.
     */
    public function stop() : void
    {
        if (! $this->isBusy()) {
            return;
        }

        posix_kill($this->workPid, SIGKILL);
        $this->getWorkState(true);
    }
}
