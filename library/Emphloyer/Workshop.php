<?php

namespace Emphloyer;

/**
 * The Workshop class runs the show.
 */
class Workshop {
  protected $boss;
  protected $run = false;

  /**
   * @param \Emphloyer\Boss $boss
   * @param array $employees
   * @return \Emphloyer\Workshop
   */
  public function __construct(Boss $boss, array $employees = array()) {
    $this->boss = $boss;

    foreach ($employees as $options) {
      for ($i = 0; $i < $options["employees"]; $i++) {
        $this->boss->allocateEmployee(new Employee($options));
      }
    }
  }

  /**
   * Run the process.
   * @param bool $keepGoing Keep running or stop after one cycle.
   */
  public function run($keepGoing = true) {
    $this->run = $keepGoing;
    do {
      pcntl_signal_dispatch();
      $this->boss->scheduleWork();
      $this->boss->delegateWork();
      $this->boss->updateProgress();
    } while($this->run);

    $this->boss->waitOnEmployees();
    $this->boss->updateProgress();
  }

  /**
   * Stop the process, this waits for all running jobs to end.
   */
  public function stop() {
    $this->run = false;
  }

  /**
   * Stop the process immediately, this kills jobs mid-process.
   */
  public function stopNow() {
    $this->run = false;
    $this->boss->stopEmployees();
    $this->boss->updateProgress();
  }
}
