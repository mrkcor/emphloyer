<?php

declare(strict_types=1);

namespace Emphloyer\Pipeline;

use DateTime;
use function array_splice;
use function array_unshift;
use function in_array;

/**
 * MemoryBackend provides you with a backend for the Pipeline that works within PHP's memory.
 * This backend is of use as an example and to build your own scripts making use of Emphloyer to run tasks.
 */
class MemoryBackend implements Backend
{
    /** @var int */
    protected $nr = 0;
    /** @var mixed[] */
    protected $queue = [];
    /** @var mixed[] */
    protected $locked = [];
    /** @var mixed[] */
    protected $failed = [];

    /** @inheritDoc */
    public function reconnect() : void
    {
    }

    /** @inheritDoc */
    public function enqueue(array $attributes, ?DateTime $notBefore = null) : array
    {
        $this->nr += 1;

        $id                       = $this->nr;
        $attributes['id']         = $id;
        $attributes['status']     = 'free';
        $attributes['not_before'] = $notBefore;
        $this->queue[]            = $attributes;

        return $attributes;
    }

    /** @inheritDoc */
    public function dequeue(array $options = []) : ?array
    {
        foreach ($this->queue as $idx => $attributes) {
            if (isset($options['exclude'])) {
                $match = ! in_array($attributes['type'], $options['exclude']);
            } else {
                if (isset($options['only'])) {
                    $match = in_array($attributes['type'], $options['only']);
                } else {
                    $match = true;
                }
            }

            if ($match) {
                $match = $attributes['not_before'] === null || ($attributes['not_before'] <= new DateTime());
            }

            if ($match) {
                array_splice($this->queue, $idx, 1);
                $attributes['status'] = 'locked';
                $this->locked[]       = $attributes;

                return $attributes;
            }
        }

        return null;
    }

    /** @inheritDoc */
    public function find($id) : ?array
    {
        foreach ($this->locked as $attributes) {
            if ($attributes['id'] === $id) {
                return $attributes;
            }
        }

        foreach ($this->failed as $attributes) {
            if ($attributes['id'] === $id) {
                return $attributes;
            }
        }

        foreach ($this->queue as $attributes) {
            if ($attributes['id'] === $id) {
                return $attributes;
            }
        }

        return null;
    }

    /** @inheritDoc */
    public function clear() : void
    {
        $this->queue  = [];
        $this->locked = [];
        $this->failed = [];
    }

    /** @inheritDoc */
    public function complete(array $attributes) : void
    {
        foreach ($this->locked as $idx => $job) {
            if ($job['id'] === $attributes['id']) {
                unset($this->locked[$idx]);

                return;
            }
        }
    }

    /** @inheritDoc */
    public function reset(array $attributes) : void
    {
        foreach ($this->failed as $idx => $job) {
            if ($job['id'] === $attributes['id']) {
                unset($this->failed[$idx]);
                break;
            }
        }

        foreach ($this->locked as $idx => $job) {
            if ($job['id'] === $attributes['id']) {
                unset($this->locked[$idx]);
                break;
            }
        }

        $attributes['status'] = 'free';
        array_unshift($this->queue, $attributes);
    }

    /** @inheritDoc */
    public function fail(array $attributes) : void
    {
        foreach ($this->locked as $idx => $job) {
            if ($job['id'] === $attributes['id']) {
                unset($this->locked[$idx]);
                break;
            }
        }

        $attributes['status'] = 'failed';
        $this->failed[]       = $attributes;
    }
}
