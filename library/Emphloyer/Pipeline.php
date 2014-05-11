<?php

namespace Emphloyer;

/**
 * A Pipeline holds jobs to be done. A backend that implements the 
 * \Emphloyer\Pipeline\Backend interface must be provided to handle the 
 * storage and retrieval of job data.
 */
class Pipeline {
  /**
   * @var \Emphloyer\Pipeline\Backend
   */
  protected $backend;

  /**
   * Instantiate a new pipeline.
   * @param \Emphloyer\Pipeline\Backend $backend Pipeline backend.
   * @return \Emphloyer\Pipeline
   */
  public function __construct(\Emphloyer\Pipeline\Backend $backend) {
    $this->backend = $backend;
  }

  /**
   * Reconnect the backend if required.
   */
  public function reconnect() {
    $this->backend->reconnect();
  }

  /**
   * Push a job onto the pipeline.
   * @param \Emphloyer\Job $job Job to enqueue
   * @param \DateTime|null $notBefore Date and time after which this job may be run
   * @return \Emphloyer\Job
   */
  public function enqueue(Job $job, \DateTime $notBefore = null) {
    $attributes = $this->backend->enqueue($this->serializeJob($job), $notBefore);
    return $this->deserializeJob($attributes);
  }

  /**
   * Get a job from the pipeline.
   * @param array $options
   * @return \Emphloyer\Job|null
   */
  public function dequeue(array $options = array()) {
    if ($attributes = $this->backend->dequeue($options)) {
      return $this->deserializeJob($attributes);
    }
  }

  /**
   * Find a specific job in the pipeline by its id.
   * @return \Emphloyer\Job|null
   */
  public function find($id) {
    if ($attributes = $this->backend->find($id)) {
      return $this->deserializeJob($attributes);
    }
  }

  /**
   * Delete all the jobs from the pipeline.
   */
  public function clear() {
    $this->backend->clear();
  }

  /**
   * Mark a job as completed.
   * @param \Emphloyer\Job $job
   */
  public function complete(Job $job) {
    $this->backend->complete($this->serializeJob($job));
  }

  /**
   * Reset a job so it can be picked up again.
   * @param \Emphloyer\Job $job
   */
  public function reset(Job $job) {
    $this->backend->reset($this->serializeJob($job));
  }

  /**
   * Mark a job as failed.
   * @param \Emphloyer\Job $job
   */
  public function fail(Job $job) {
    $this->backend->fail($this->serializeJob($job));
  }

  /**
   * Convert a job into an array that can be passed on to a backend.
   * @param \Emphloyer\Job $job
   * @return array
   */
  protected function serializeJob(Job $job) {
    $attributes = $job->getAttributes();
    $attributes['className'] = get_class($job);
    $attributes['type'] = $job->getType();
    return $attributes;
  }

  /**
   * Convert an array provided by a backend into a Job instance.
   * @param array $attributes
   * @return \Emphloyer\Job
   */
  protected function deserializeJob($attributes) {
    if (isset($attributes['className'])) {
      $className = $attributes['className'];
      $job = new $className();
      unset($attributes['className']);
      $job->setType($attributes['type']);
      unset($attributes['type']);
      $job->setAttributes($attributes);
      return $job;
    }
  }
}
