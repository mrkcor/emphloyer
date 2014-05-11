<?php

namespace Emphloyer\Pipeline;

/**
 * MemoryBackend provides you with a backend for the Pipeline that works within PHP's memory. 
 * This backend is of use as an example and to build your own scripts making use of Emphloyer to run tasks.
 */
class MemoryBackend implements Backend {
  protected $nr = 0;
  protected $queue = array();
  protected $locked = array();
  protected $failed = array();

  /**
   * Reconnect the backend.
   */
  public function reconnect() {
  }

  /**
   * Push a job onto the pipeline.
   * @param array $attributes Job attributes to save (must include the class name as 'className'
   * @param \DateTime|null $notBefore Date and time after which this job may be run
   * @return array $attributes Updated job attributes, the Pipeline will instantiate a new job instance with these updated attributes (this can be useful to pass a job id or some other attribute of importance back to the caller of this method).
   */
  public function enqueue($attributes, \DateTime $notBefore = null) {
    $this->nr += 1;
    $id = $this->nr;
    $attributes['id'] = $id;
    $attributes['status'] = 'free';
    $attributes['not_before'] = $notBefore;
    $this->queue[] = $attributes;
    return $attributes;
  }

  /**
   * Get a job from the pipeline and return its attributes.
   * @param array $options
   * @return array|null
   */
  public function dequeue(array $options = array()) {
    $match = false;
    foreach ($this->queue as $idx => $attributes) {
      if (isset($options["exclude"])) {
        $match = !in_array($attributes["type"], $options["exclude"]);
      } else if (isset($options["only"])) {
        $match = in_array($attributes["type"], $options["only"]);
      } else {
        $match = true;
      }

      if ($match) {
        $match = is_null($attributes['not_before']) || ($attributes['not_before'] <= new \DateTime());
      }

      if ($match) {
        array_splice($this->queue, $idx, 1);
        $attributes['status'] = 'locked';
        $this->locked[] = $attributes;
        return $attributes;
      }
    }
  }

  /**
   * Find a specific job in the pipeline using its id and return its attributes.
   * @param mixed $id
   * @return array|null
   */
  public function find($id) {
    foreach ($this->locked as $attributes) {
      if ($attributes['id'] == $id) {
        return $attributes;
      }
    }

    foreach ($this->failed as $attributes) {
      if ($attributes['id'] == $id) {
        return $attributes;
      }
    }

    foreach ($this->queue as $attributes) {
      if ($attributes['id'] == $id) {
        return $attributes;
      }
    }

  }

  /**
   * Delete all the jobs from the pipeline.
   */
  public function clear() {
    $this->queue = array();
    $this->locked = array();
    $this->failed = array();
  }

  /**
   * Mark a job as completed.
   * @param array $attributes
   */
  public function complete($attributes) {
    foreach ($this->locked as $idx => $job) {
      if ($job['id'] == $attributes['id']) {
        unset($this->locked[$idx]);
        return;
      }
    }
  }

  /**
   * Reset a job so it can be picked up again.
   * @param array $attributes
   */
  public function reset($attributes) {
    foreach ($this->failed as $idx => $job) {
      if ($job['id'] == $attributes['id']) {
        unset($this->failed[$idx]);
        break;
      }
    }

    foreach ($this->locked as $idx => $job) {
      if ($job['id'] == $attributes['id']) {
        unset($this->locked[$idx]);
        break;
      }
    }

    $attributes['status'] = 'free';
    array_unshift($this->queue, $attributes);
  }

  /**
   * Mark a job as failed.
   * @param array $attributes
   */
  public function fail($attributes) {
    foreach ($this->locked as $idx => $job) {
      if ($job['id'] == $attributes['id']) {
        unset($this->locked[$idx]);
        break;
      }
    }

    $attributes['status'] = 'failed';
    $this->failed[] = $attributes;
  }
}
