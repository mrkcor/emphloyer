<?php

namespace Emphloyer;

class JobSerDes
{
    /**
     * Convert a job into an array that can be passed on to a backend.
     * @param \Emphloyer\Job $job
     * @return array
     */
    public function serialize(Job $job)
    {
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
    public function deserialize($attributes)
    {
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
