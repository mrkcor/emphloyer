<?php

declare(strict_types=1);

namespace Emphloyer;

use function get_class;

class JobSerDes
{
    /**
     * Convert a job into an array that can be passed on to a backend.
     *
     * @return mixed[]
     */
    public function serialize(Job $job) : array
    {
        $attributes              = $job->getAttributes();
        $attributes['className'] = get_class($job);
        $attributes['type']      = $job->getType();

        return $attributes;
    }

    /**
     * Convert an array provided by a backend into a Job instance.
     *
     * @param mixed[] $attributes
     */
    public function deserialize(array $attributes) : ?Job
    {
        $className = $attributes['className'] ?? null;

        if (! $className) {
            return null;
        }

        $className = $attributes['className'];
        $job       = new $className();
        unset($attributes['className']);
        $job->setType($attributes['type']);
        unset($attributes['type']);
        $job->setAttributes($attributes);

        return $job;
    }
}
