<?php

declare(strict_types=1);

namespace Emphloyer;

/**
 * Interface that must be implemented when defining jobs to be run through
 * Emphloyer
 */
interface Job
{
    /**
     * Once a job has been enqueued it must have some sort of unique id.
     *
     * @return mixed
     */
    public function getId();

    /**
     * Return the type of the job. Setting the type in your own implementations
     * allows you to control how much bandwith particular jobs get.
     */
    public function getType() : string;

    /**
     * Set the type of the job.
     */
    public function setType(string $type) : void;

    /**
     * If this method returns true then this job will be retried on failure.
     */
    public function mayTryAgain() : bool;

    /**
     * This method must contain the logic that your job must execute.
     */
    public function perform() : void;

    /**
     * Return the attributes to store when queueing this job.
     *
     * @return mixed[]
     */
    public function getAttributes() : array;

    /**
     * Set the attributes for this job (used when loading a job).
     *
     * @param mixed[] $attributes
     */
    public function setAttributes(array $attributes) : void;
}
