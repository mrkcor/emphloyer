<?php

namespace Emphloyer\Job;

/**
 * Implement your own ForkHooks to execute code that must be executed prior to each job after it has been forked. This is suitable for tasks such as re-establishing a database connection or debugging.
 */
interface ForkHook {
  /**
   * @param \Emphloyer\Job $job
   */
  public function run(\Emphloyer\Job $job);
}
