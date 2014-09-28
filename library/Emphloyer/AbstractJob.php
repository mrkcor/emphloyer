<?php

namespace Emphloyer;

/**
 * AbstractJob can be extended to implement your own Job classes.
 */
abstract class AbstractJob implements Job {
  protected $attributes = array();
  protected $type = 'job';

  /**
   * Once a job has been enqueued it must have some sort of unique id.
   * @return mixed
   */
  public function getId() {
    if (isset($this->attributes['id'])) {
      return $this->attributes['id'];
    }
  }

  /**
   * Return the type of the job. Setting the type in your own implementations
   * allows you to control how much bandwith particular jobs get.
   * @return string
   */
  public function getType() {
    return $this->type;
  }

  /**
   * Set the type of the job.
   * @param string $type
   * @return void
   */
  public function setType($type) {
    $this->type = $type;
  }

  /**
   * If this method returns true then this job will be retried on failure.
   * @return bool
   */
  public function mayTryAgain() {
    return false;
  }

  /**
   * Return the attributes to store when queueing this job.
   * @return array
   */
  public function getAttributes() {
    return $this->attributes;
  }

  /**
   * Set the attributes for this job (used when loading a job).
   * @param array $attributes
   */
  public function setAttributes($attributes) {
    $this->attributes = $attributes;
  }
}
