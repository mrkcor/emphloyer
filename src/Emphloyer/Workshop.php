<?php

declare(strict_types=1);

namespace Emphloyer;

use function pcntl_signal_dispatch;

/**
 * The Workshop class runs the show.
 */
class Workshop
{
    /** @var Boss */
    protected $boss;
    /** @var bool */
    protected $run = false;

    /**
     * @param Employee[] $employees
     */
    public function __construct(Boss $boss, array $employees = [])
    {
        $this->boss = $boss;

        foreach ($employees as $options) {
            for ($i = 0; $i < $options['employees']; $i++) {
                $this->boss->allocateEmployee(new Employee($options));
            }
        }
    }

    /**
     * Run the process.
     *
     * @param bool $keepGoing Keep running or stop after one cycle.
     */
    public function run(bool $keepGoing = true) : void
    {
        $this->run = $keepGoing;
        do {
            pcntl_signal_dispatch();
            $this->boss->scheduleWork();
            $this->boss->delegateWork();
            $this->boss->updateProgress();
        } while ($this->run);

        $this->boss->waitOnEmployees();
        $this->boss->updateProgress();
    }

    /**
     * Stop the process, this waits for all running jobs to end.
     */
    public function stop() : void
    {
        $this->run = false;
    }

    /**
     * Stop the process immediately, this kills jobs mid-process.
     */
    public function stopNow() : void
    {
        $this->run = false;
        $this->boss->stopEmployees();
        $this->boss->updateProgress();
    }
}
