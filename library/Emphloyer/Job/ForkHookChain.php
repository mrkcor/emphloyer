<?php

namespace Emphloyer\Job;

/**
 * Class to run a series of ForkHook instances
 */
class ForkHookChain {
  protected $hooks = array();

  /**
   * Add a hook to the chain.
   * @param ForkHook $hook
   */
  public function add(ForkHook $hook) {
    $this->hooks[] = $hook;
  }

  /**
   * Run the chain.
   */
  public function run(\Emphloyer\Job $job) {
    foreach ($this->hooks as $hook) {
      $hook->run($job);
    }
  }
}
