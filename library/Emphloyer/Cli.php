<?php

namespace Emphloyer;

/**
 * Cli is the class used by the command line runner to execute input commands.
 */
class Cli {
  protected $lastSignal;
  protected $pipeline;
  protected $numberOfEmployees = 0;

  /**
   * Configure with PHP code from a file.
   */
  public function configure($filename) {
    require $filename;
    $this->numberOfEmployees = $numberOfEmployees;
    $this->pipeline = new Pipeline($pipelineBackend);
  }

  /**
   * Run jobs.
   */
  public function run() {
    $this->workshop = new Workshop(new Boss($this->pipeline), $this->numberOfEmployees);
    
    declare(ticks = 100);
    pcntl_signal(\SIGINT, array($this, 'handleSignal'));
    $this->workshop->run();
  }

  /**
   * Clear all jobs from the pipeline.
   */
  public function clear() {
    $this->pipeline->clear();
  }

  /**
   * Signal handler.
   * @param int $signo
   */
  public function handleSignal($signo) {
    switch ($signo) {
    case \SIGINT:
      if (!is_null($this->lastSignal) && $this->lastSignal <= (time() - 5)) {
        $this->workshop->stopNow();
      } else {
        $this->lastSignal = time();
        $this->workshop->stop();
      }
      break;
    }
  }
}
