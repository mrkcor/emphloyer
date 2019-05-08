<?php

declare(strict_types=1);

namespace Emphloyer;

/**
 * AbstractJob can be extended to implement your own Job classes.
 */
abstract class AbstractJob implements Job
{
    /** @var mixed[] */
    protected $attributes = [];
    /** @var string */
    protected $type = 'job';

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return $this->attributes['id'] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function getType() : string
    {
        return $this->type;
    }

    /**
     * @inheritDoc
     */
    public function setType(string $type) : void
    {
        $this->type = $type;
    }

    /**
     * @inheritDoc
     */
    public function mayTryAgain() : bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getAttributes() : array
    {
        return $this->attributes;
    }

    /**
     * @inheritDoc
     */
    public function setAttributes(array $attributes) : void
    {
        $this->attributes = $attributes;
    }
}
