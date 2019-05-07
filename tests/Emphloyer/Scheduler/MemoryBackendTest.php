<?php

namespace Emphloyer\Scheduler;

class MemoryBackendTest extends BackendTestCase
{
    public function setUp() : void
    {
        $this->backend = new MemoryBackend();
        parent::setUp();
    }
}
