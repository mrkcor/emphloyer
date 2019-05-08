<?php

declare(strict_types=1);

namespace Emphloyer\Pipeline;

use Emphloyer\AbstractJob;

class BackendTestJob extends AbstractJob
{
    public function setName(?string $name = null) : void
    {
        $this->attributes['name'] = $name;
    }

    public function getName() : ?string
    {
        return $this->attributes['name'];
    }

    public function getStatus() : ?string
    {
        return $this->attributes['status'];
    }

    public function perform() : void
    {
    }
}
