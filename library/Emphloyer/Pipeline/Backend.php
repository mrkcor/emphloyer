<?php

namespace Emphloyer\Pipeline;

/**
 * Implement this interface to build your own Pipeline backend.
 */
interface Backend {
  /**
   * Reconnect the backend.
   */
  public function reconnect();

  /**
   * Push a job onto the pipeline.
   * @param array $attributes Job attributes to save (must include the class name as 'className'
   * @return array $attributes Updated job attributes, the Pipeline will instantiate a new job instance with these updated attributes (this can be useful to pass a job id or some other attribute of importance back to the caller of this method).
   */
  public function enqueue($attributes);

  /**
   * Get a job from the pipeline and return its attributes.
   * @param array $options
   * @return array|null
   */
  public function dequeue(array $options = array());

  /**
   * Find a specific job in the pipeline using its id and return its attributes.
   * @param mixed $id
   * @return array|null
   */
  public function find($id);

  /**
   * Delete all the jobs from the pipeline.
   */
  public function clear();

  /**
   * Mark a job as completed.
   * @param array $attributes
   */
  public function complete($attributes);

  /**
   * Reset a job so it can be picked up again.
   * @param array $attributes
   */
  public function reset($attributes);

  /**
   * Mark a job as failed.
   * @param array $attributes
   */
  public function fail($attributes);
}
