<?php

namespace Emphloyer\Scheduler;

class MemoryBackendTest extends BackendTestCase
{
    public function setUp()
    {
        $this->backend = new MemoryBackend();
        parent::setUp();
    }
}
