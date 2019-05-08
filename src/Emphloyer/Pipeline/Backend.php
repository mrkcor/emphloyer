<?php

declare(strict_types=1);

namespace Emphloyer\Pipeline;

use DateTime;

/**
 * Implement this interface to build your own Pipeline backend.
 */
interface Backend
{
    /**
     * Reconnect the backend.
     */
    public function reconnect() : void;

    /**
     * Push a job onto the pipeline.
     *
     * @param mixed[]       $attributes Job attributes to save (must include the class name as 'className'
     * @param DateTime|null $notBefore  Date and time after which this job may be run
     *
     * @return mixed[] Updated job attributes, the Pipeline will instantiate a new job instance with these updated
     *                 attributes (this can be useful to pass a job id or some other attribute of importance back to
     *                 the caller of this method).
     */
    public function enqueue(array $attributes, ?DateTime $notBefore = null) : array;

    /**
     * Get a job from the pipeline and return its attributes.
     *
     * @param mixed[] $options
     *
     * @return mixed[]|null
     */
    public function dequeue(array $options = []) : ?array;

    /**
     * Find a specific job in the pipeline using its id and return its attributes.
     *
     * @param mixed $id
     *
     * @return mixed[]|null
     */
    public function find($id) : ?array;

    /**
     * Delete all the jobs from the pipeline.
     */
    public function clear() : void;

    /**
     * Mark a job as completed.
     *
     * @param mixed[] $attributes
     */
    public function complete(array $attributes) : void;

    /**
     * Reset a job so it can be picked up again.
     *
     * @param mixed[] $attributes
     */
    public function reset(array $attributes) : void;

    /**
     * Mark a job as failed.
     *
     * @param mixed[] $attributes
     */
    public function fail(array $attributes) : void;
}
