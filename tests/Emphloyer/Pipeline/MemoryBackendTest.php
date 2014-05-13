<?php

namespace Emphloyer\Pipeline;

class MemoryBackendTest extends BackendTestCase {
  public function setUp() {
    $this->backend = new MemoryBackend();
    parent::setUp();
  }
}
