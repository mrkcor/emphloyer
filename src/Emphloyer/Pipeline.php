<?php

declare(strict_types=1);

namespace Emphloyer;

use DateTime;
use Emphloyer\Pipeline\Backend;

/**
 * A Pipeline holds jobs to be done. A backend that implements the
 * \Emphloyer\Pipeline\Backend interface must be provided to handle the
 * storage and retrieval of job data.
 */
class Pipeline
{
    /** @var Backend */
    protected $backend;
    /** @var JobSerDes */
    protected $jobSerDes;

    /**
     * Instantiate a new pipeline.
     *
     * @param Backend $backend Pipeline backend.
     */
    public function __construct(Backend $backend)
    {
        $this->backend   = $backend;
        $this->jobSerDes = new JobSerDes();
    }

    /**
     * Reconnect the backend if required.
     */
    public function reconnect() : void
    {
        $this->backend->reconnect();
    }

    /**
     * Push a job onto the pipeline.
     *
     * @param Job           $job       Job to enqueue
     * @param DateTime|null $notBefore Date and time after which this job may be run
     */
    public function enqueue(Job $job, ?DateTime $notBefore = null) : Job
    {
        $attributes = $this->backend->enqueue($this->serializeJob($job), $notBefore);

        return $this->deserializeJob($attributes);
    }

    /**
     * Convert a job into an array that can be passed on to a backend.
     *
     * @return mixed[]
     */
    protected function serializeJob(Job $job) : array
    {
        return $this->jobSerDes->serialize($job);
    }

    /**
     * Convert an array provided by a backend into a Job instance.
     *
     * @param mixed[] $attributes
     */
    protected function deserializeJob(array $attributes) : Job
    {
        return $this->jobSerDes->deserialize($attributes);
    }

    /**
     * Get a job from the pipeline.
     *
     * @param mixed[] $options
     */
    public function dequeue(array $options = []) : ?Job
    {
        $attributes = $this->backend->dequeue($options);
        if ($attributes) {
            return $this->deserializeJob($attributes);
        }

        return null;
    }

    /**
     * Find a specific job in the pipeline by its id.
     *
     * @param mixed $id
     */
    public function find($id) : ?Job
    {
        $attributes = $this->backend->find($id);
        if ($attributes) {
            return $this->deserializeJob($attributes);
        }

        return null;
    }

    /**
     * Delete all the jobs from the pipeline.
     */
    public function clear() : void
    {
        $this->backend->clear();
    }

    /**
     * Mark a job as completed.
     */
    public function complete(Job $job) : void
    {
        $this->backend->complete($this->serializeJob($job));
    }

    /**
     * Reset a job so it can be picked up again.
     */
    public function reset(Job $job) : void
    {
        $this->backend->reset($this->serializeJob($job));
    }

    /**
     * Mark a job as failed.
     */
    public function fail(Job $job) : void
    {
        $this->backend->fail($this->serializeJob($job));
    }
}
