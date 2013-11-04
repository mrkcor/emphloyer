<?php

namespace Emphloyer\Pipeline;

class MemoryBackendTestJob extends \Emphloyer\AbstractJob {
  public function setName($name) {
    $this->attributes['name'] = $name;
  }

  public function getName() {
    return $this->attributes['name'];
  }

  public function getStatus() {
    return $this->attributes['status'];
  }

  public function perform() {
  }
}

class MemoryBackendTest extends \PHPUnit_Framework_TestCase {
  public function setUp() {
    $this->backend = new MemoryBackend();
    $this->pipeline = new \Emphloyer\Pipeline($this->backend);
  }

  public function testEnqueue() {
    $job = new MemoryBackendTestJob();
    $job->setName('Job 1');

    $queuedJob = $this->pipeline->enqueue($job);
    $this->assertEquals(1, $queuedJob->getId());
    $this->assertEquals('Job 1', $queuedJob->getName());
    $this->assertEquals('free', $queuedJob->getStatus());

    $job = new MemoryBackendTestJob();
    $job->setName('Job 2');

    $queuedJob = $this->pipeline->enqueue($job);
    $this->assertEquals(2, $queuedJob->getId());
    $this->assertEquals('Job 2', $queuedJob->getName());
    $this->assertEquals('free', $queuedJob->getStatus());
  }

  public function testDequeue() {
    $job = new MemoryBackendTestJob();
    $job->setName('Job 1');
    $this->pipeline->enqueue($job);
    
    $job = new MemoryBackendTestJob();
    $job->setName('Job 2');
    $this->pipeline->enqueue($job);

    $job = $this->pipeline->dequeue();
    $this->assertEquals(1, $job->getId());
    $this->assertEquals('Job 1', $job->getName());
    $this->assertEquals('locked', $job->getStatus());

    $job = $this->pipeline->dequeue();
    $this->assertEquals(2, $job->getId());
    $this->assertEquals('Job 2', $job->getName());
    $this->assertEquals('locked', $job->getStatus());

    $this->assertNull($this->pipeline->dequeue());
  }

  public function testFindJob() {
    $job1 = new MemoryBackendTestJob();
    $job1->setName('Job 1');

    $job2 = new MemoryBackendTestJob();
    $job2->setName('Job 2');

    $job3 = new MemoryBackendTestJob();
    $job3->setName('Job 3');

    $job1 = $this->pipeline->enqueue($job1);
    $job2 = $this->pipeline->enqueue($job2);
    $job3 = $this->pipeline->enqueue($job3);

    $lockedJob = $this->pipeline->dequeue();
    $failedJob = $this->pipeline->dequeue();
    $this->pipeline->fail($failedJob);

    $foundJob = $this->pipeline->find(1);
    $this->assertEquals($job1->getId(), $foundJob->getId());
    $this->assertEquals('Job 1', $foundJob->getName());
    $this->assertEquals('locked', $foundJob->getStatus());

    $foundJob = $this->pipeline->find(2);
    $this->assertEquals($job2->getId(), $foundJob->getId());
    $this->assertEquals('Job 2', $foundJob->getName());
    $this->assertEquals('failed', $foundJob->getStatus());

    $foundJob = $this->pipeline->find(3);
    $this->assertEquals($job3->getId(), $foundJob->getId());
    $this->assertEquals('Job 3', $foundJob->getName());
    $this->assertEquals('free', $foundJob->getStatus());
  }

  public function testClear() {
    $job1 = new MemoryBackendTestJob();
    $job1->setName('Job 1');

    $job2 = new MemoryBackendTestJob();
    $job2->setName('Job 2');

    $job3 = new MemoryBackendTestJob();
    $job3->setName('Job 3');

    $job1 = $this->pipeline->enqueue($job1);
    $job2 = $this->pipeline->enqueue($job2);
    $job3 = $this->pipeline->enqueue($job3);

    $lockedJob = $this->pipeline->dequeue();
    $failedJob = $this->pipeline->dequeue();
    $this->pipeline->fail($failedJob);

    $this->pipeline->clear();
    $this->assertNull($this->pipeline->find(1));
    $this->assertNull($this->pipeline->find(2));
    $this->assertNull($this->pipeline->find(3));
  }

  public function testComplete() {
    $job = new MemoryBackendTestJob();
    $job->setName('Job 1');
    $job = $this->pipeline->enqueue($job);
    $this->assertEquals(1, $job->getId());

    $job = $this->pipeline->dequeue();
    $this->assertEquals(1, $job->getId());
    $this->assertNotNull($this->pipeline->find(1));

    $this->pipeline->complete($job);
    $this->assertNull($this->pipeline->find(1));
  }

  public function testFail() {
    $job = new MemoryBackendTestJob();
    $job->setName('Job 1');
    $job = $this->pipeline->enqueue($job);
    $this->assertEquals(1, $job->getId());

    $job = $this->pipeline->dequeue();
    $this->assertEquals(1, $job->getId());
    $this->assertNotNull($this->pipeline->find(1));

    $this->pipeline->fail($job);
    $job = $this->pipeline->find(1);
    $this->assertNotNull($job);
    $this->assertEquals('failed', $job->getStatus());
    $this->assertNull($this->pipeline->dequeue());
  }

  public function testResetFailedJob() {
    $job = new MemoryBackendTestJob();
    $job->setName('Job 1');
    $job = $this->pipeline->enqueue($job);
    $this->assertEquals(1, $job->getId());

    $job = $this->pipeline->dequeue();
    $this->assertEquals(1, $job->getId());
    $this->assertNotNull($this->pipeline->find(1));

    $this->pipeline->fail($job);
    $this->assertEquals('failed', $this->pipeline->find(1)->getStatus());
    $this->pipeline->reset($job);
    $this->assertEquals('free', $this->pipeline->find(1)->getStatus());

    $job = $this->pipeline->dequeue();
    $this->assertEquals(1, $job->getId());
    $this->assertNotNull($this->pipeline->find(1));
  }

  public function testResetLockedJob() {
    $job = new MemoryBackendTestJob();
    $job->setName('Job 1');
    $job = $this->pipeline->enqueue($job);
    $this->assertEquals(1, $job->getId());

    $job = $this->pipeline->dequeue();
    $this->assertEquals(1, $job->getId());
    $this->assertNotNull($this->pipeline->find(1));
    $this->assertEquals('locked', $this->pipeline->find(1)->getStatus());

    $this->assertNull($this->pipeline->dequeue());
    $this->pipeline->reset($job);
    $this->assertNotNull($this->pipeline->find(1));
    $this->assertEquals('free', $this->pipeline->find(1)->getStatus());

    $job = $this->pipeline->dequeue();
    $this->assertEquals(1, $job->getId());
    $this->assertNotNull($this->pipeline->find(1));
    $this->assertEquals('locked', $this->pipeline->find(1)->getStatus());
  }
}
