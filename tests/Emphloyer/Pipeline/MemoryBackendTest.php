<?php

namespace Emphloyer\Pipeline;

class MemoryBackendTest extends BackendTestCase
{
    public function setUp() : void
    {
        $this->backend = new MemoryBackend();
        parent::setUp();
    }
}
