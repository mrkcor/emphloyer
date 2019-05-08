<?php

declare(strict_types=1);

namespace Emphloyer;

use Emphloyer\Pipeline\Backend;
use InvalidArgumentException;
use const SIGINT;
use const SIGTERM;
use function pcntl_signal;
use function time;

/**
 * Cli is the class used by the command line runner to execute input commands.
 */
class Cli
{
    /** @var ?int */
    protected $lastSignal;
    /** @var Workshop */
    protected $workshop;
    /** @var Pipeline */
    protected $pipeline;
    /** @var Scheduler|null */
    protected $scheduler;
    /** @var mixed[] */
    protected $employees = [];

    /**
     * Configure with PHP code from a file.
     */
    public function configure(string $filename) : void
    {
        $employees        = [];
        $pipelineBackend  = null;
        $schedulerBackend = null;

        require $filename;

        $this->employees = $employees;

        if (! $pipelineBackend instanceof Backend) {
            throw new InvalidArgumentException('Pipeline backend is not configured correctly.');
        }

        $this->pipeline = new Pipeline($pipelineBackend);

        if ($schedulerBackend === null) {
            return;
        }

        $this->scheduler = new Scheduler($schedulerBackend);
    }

    /**
     * Run jobs.
     */
    public function run() : void
    {
        $this->workshop = new Workshop(new Boss($this->pipeline, $this->scheduler), $this->employees);

        declare(ticks=100);
        pcntl_signal(SIGINT, [$this, 'handleSignal']);
        pcntl_signal(SIGTERM, [$this, 'handleSignal']);
        $this->workshop->run();
    }

    /**
     * Clear all jobs from the pipeline.
     */
    public function clear() : void
    {
        $this->pipeline->clear();
    }

    /**
     * Signal handler.
     */
    public function handleSignal(int $signo) : void
    {
        switch ($signo) {
            case SIGINT:
            case SIGTERM:
                if ($this->lastSignal !== null && $this->lastSignal <= (time() - 5)) {
                    $this->workshop->stopNow();
                } else {
                    $this->lastSignal = time();
                    $this->workshop->stop();
                }
                break;
        }
    }
}
