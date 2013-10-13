<?php

namespace Emphloyer;

/**
 * Interface that must be implemented when defining jobs to be run through
 * Emphloyer
 */
interface Job {
  /**
   * Once a job has been enqueued it must have some sort of unique id.
   * @return mixed
   */
  public function getId();

  /**
   * If this method returns true then this job will be retried on failure.
   * @return bool
   */
  public function mayTryAgain();

  /**
   * This method must contain the logic that your job must execute.
   * @return void
   */
  public function perform();

  /**
   * Return the attributes to store when queueing this job.
   * @return array
   */
  public function getAttributes();

  /**
   * Set the attributes for this job (used when loading a job).
   * @param array $attributes
   */
  public function setAttributes($attributes);
}
