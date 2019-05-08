<?php

declare(strict_types=1);

namespace Emphloyer\Pipeline;

use DateTime;
use Emphloyer\Pipeline;
use PHPUnit\Framework\TestCase;
use function sleep;

class BackendTestCase extends TestCase
{
    /** @var Pipeline */
    protected $pipeline;

    public function setUp() : void
    {
        $this->pipeline = new Pipeline($this->backend);
    }

    public function testEnqueue() : void
    {
        $job = new BackendTestJob();
        $job->setName('Job 1');
        $job->setType('misc');

        $queuedJob1 = $this->pipeline->enqueue($job);
        $this->assertNotNull($queuedJob1->getId());
        $this->assertEquals('Job 1', $queuedJob1->getName());
        $this->assertEquals('free', $queuedJob1->getStatus());
        $this->assertEquals('misc', $queuedJob1->getType());

        $job = new BackendTestJob();
        $job->setName('Job 2');

        $queuedJob2 = $this->pipeline->enqueue($job);
        $this->assertNotNull($queuedJob2->getId());
        $this->assertNotEquals($queuedJob2->getId(), $queuedJob1);
        $this->assertEquals('Job 2', $queuedJob2->getName());
        $this->assertEquals('free', $queuedJob2->getStatus());
        $this->assertEquals('job', $queuedJob2->getType());
    }

    public function testDequeue() : void
    {
        $job = new BackendTestJob();
        $job->setName('Job 1');
        $job->setType('misc');
        $queuedJob1 = $this->pipeline->enqueue($job);

        sleep(1);
        $job = new BackendTestJob();
        $job->setName('Job 2');
        $queuedJob2 = $this->pipeline->enqueue($job);

        $job = $this->pipeline->dequeue();
        $this->assertEquals($queuedJob1->getId(), $job->getId());
        $this->assertEquals('Job 1', $job->getName());
        $this->assertEquals('locked', $job->getStatus());

        $job = $this->pipeline->dequeue();
        $this->assertEquals($queuedJob2->getId(), $job->getId());
        $this->assertEquals('Job 2', $job->getName());
        $this->assertEquals('locked', $job->getStatus());

        $this->assertNull($this->pipeline->dequeue());
    }

    public function testDequeueOnlyMatchingTypes() : void
    {
        $job = new BackendTestJob();
        $job->setName('Job 1');
        $job->setType('type2');
        $queuedJob1 = $this->pipeline->enqueue($job);
        sleep(1);

        $job = new BackendTestJob();
        $job->setName('Job 2');
        $queuedJob2 = $this->pipeline->enqueue($job);

        $job = new BackendTestJob();
        $job->setName('Job 3');
        $job->setType('type1');
        $queuedJob3 = $this->pipeline->enqueue($job);

        $job = $this->pipeline->dequeue(['only' => ['type1', 'type2']]);
        $this->assertEquals($queuedJob1->getId(), $job->getId());
        $this->assertEquals('Job 1', $job->getName());
        $this->assertEquals('locked', $job->getStatus());
        $this->assertEquals('type2', $job->getType());

        $job = $this->pipeline->dequeue(['only' => ['type1', 'type2']]);
        $this->assertEquals($queuedJob3->getId(), $job->getId());
        $this->assertEquals('Job 3', $job->getName());
        $this->assertEquals('locked', $job->getStatus());
        $this->assertEquals('type1', $job->getType());

        $this->assertNull($this->pipeline->dequeue(['only' => ['type1', 'type2']]));
    }

    public function testDequeueSkipsExcludedTypes() : void
    {
        $job = new BackendTestJob();
        $job->setName('Job 1');
        $job->setType('type2');
        $queuedJob1 = $this->pipeline->enqueue($job);
        sleep(1);

        $job = new BackendTestJob();
        $job->setName('Job 2');
        $queuedJob2 = $this->pipeline->enqueue($job);

        $job = new BackendTestJob();
        $job->setName('Job 3');
        $job->setType('type1');
        $queuedJob3 = $this->pipeline->enqueue($job);

        $job = $this->pipeline->dequeue(['exclude' => ['type1']]);
        $this->assertEquals($queuedJob1->getId(), $job->getId());
        $this->assertEquals('Job 1', $job->getName());
        $this->assertEquals('locked', $job->getStatus());
        $this->assertEquals('type2', $job->getType());

        $job = $this->pipeline->dequeue(['exclude' => ['type1']]);
        $this->assertEquals($queuedJob2->getId(), $job->getId());
        $this->assertEquals('Job 2', $job->getName());
        $this->assertEquals('locked', $job->getStatus());
        $this->assertEquals('job', $job->getType());

        $this->assertNull($this->pipeline->dequeue(['exclude' => ['type1']]));
    }

    public function testDequeueNotBefore() : void
    {
        $job = new BackendTestJob();
        $job->setName('Job 1');
        $job->setType('misc');
        $queuedJob1 = $this->pipeline->enqueue($job, new DateTime('+2 seconds'));

        $job = new BackendTestJob();
        $job->setName('Job 2');
        $queuedJob2 = $this->pipeline->enqueue($job);

        $job = $this->pipeline->dequeue();
        $this->assertEquals($queuedJob2->getId(), $job->getId());
        $this->assertEquals('Job 2', $job->getName());
        $this->assertEquals('locked', $job->getStatus());

        sleep(1);
        $this->assertNull($this->pipeline->dequeue());

        sleep(1);
        $job = $this->pipeline->dequeue();
        $this->assertEquals($queuedJob1->getId(), $job->getId());
        $this->assertEquals('Job 1', $job->getName());
        $this->assertEquals('locked', $job->getStatus());

        $this->assertNull($this->pipeline->dequeue());
    }

    public function testFindJob() : void
    {
        $job1 = new BackendTestJob();
        $job1->setName('Job 1');

        $job2 = new BackendTestJob();
        $job2->setName('Job 2');

        $job3 = new BackendTestJob();
        $job3->setName('Job 3');

        $job1 = $this->pipeline->enqueue($job1);
        sleep(1);
        $job2 = $this->pipeline->enqueue($job2);
        sleep(1);
        $job3 = $this->pipeline->enqueue($job3);

        $lockedJob = $this->pipeline->dequeue();
        $failedJob = $this->pipeline->dequeue();
        $this->pipeline->fail($failedJob);

        $foundJob = $this->pipeline->find($job1->getId());
        $this->assertEquals($job1->getId(), $foundJob->getId());
        $this->assertEquals('Job 1', $foundJob->getName());
        $this->assertEquals('locked', $foundJob->getStatus());

        $foundJob = $this->pipeline->find($job2->getId());
        $this->assertEquals($job2->getId(), $foundJob->getId());
        $this->assertEquals('Job 2', $foundJob->getName());
        $this->assertEquals('failed', $foundJob->getStatus());

        $foundJob = $this->pipeline->find($job3->getId());
        $this->assertEquals($job3->getId(), $foundJob->getId());
        $this->assertEquals('Job 3', $foundJob->getName());
        $this->assertEquals('free', $foundJob->getStatus());
    }

    public function testClear() : void
    {
        $job1 = new BackendTestJob();
        $job1->setName('Job 1');

        $job2 = new BackendTestJob();
        $job2->setName('Job 2');

        $job3 = new BackendTestJob();
        $job3->setName('Job 3');

        $job1 = $this->pipeline->enqueue($job1);
        $job2 = $this->pipeline->enqueue($job2);
        $job3 = $this->pipeline->enqueue($job3);

        $lockedJob = $this->pipeline->dequeue();
        $failedJob = $this->pipeline->dequeue();
        $this->pipeline->fail($failedJob);

        $this->pipeline->clear();
        $this->assertNull($this->pipeline->find($job1->getId()));
        $this->assertNull($this->pipeline->find($job2->getId()));
        $this->assertNull($this->pipeline->find($job3->getId()));
    }

    public function testComplete() : void
    {
        $job = new BackendTestJob();
        $job->setName('Job 1');
        $queuedJob = $this->pipeline->enqueue($job);

        $job = $this->pipeline->dequeue();
        $this->assertEquals($queuedJob->getId(), $job->getId());
        $this->assertNotNull($this->pipeline->find($queuedJob->getId()));

        $this->pipeline->complete($job);
        $this->assertNull($this->pipeline->find($queuedJob->getId()));
    }

    public function testFail() : void
    {
        $job = new BackendTestJob();
        $job->setName('Job 1');
        $queuedJob = $this->pipeline->enqueue($job);

        $job = $this->pipeline->dequeue();
        $this->assertEquals($queuedJob->getId(), $job->getId());
        $this->assertNotNull($this->pipeline->find($queuedJob->getId()));

        $this->pipeline->fail($job);
        $job = $this->pipeline->find($queuedJob->getId());
        $this->assertNotNull($job);
        $this->assertEquals('failed', $job->getStatus());
        $this->assertNull($this->pipeline->dequeue());
    }

    public function testResetFailedJob() : void
    {
        $job = new BackendTestJob();
        $job->setName('Job 1');
        $queuedJob = $this->pipeline->enqueue($job);

        $job = $this->pipeline->dequeue();
        $this->assertEquals($queuedJob->getId(), $job->getId());
        $this->assertNotNull($this->pipeline->find($queuedJob->getId()));

        $this->pipeline->fail($job);
        $this->assertEquals('failed', $this->pipeline->find($queuedJob->getId())->getStatus());
        $this->pipeline->reset($job);
        $this->assertEquals('free', $this->pipeline->find($queuedJob->getId())->getStatus());

        $job = $this->pipeline->dequeue();
        $this->assertEquals($queuedJob->getId(), $job->getId());
        $this->assertNotNull($this->pipeline->find($queuedJob->getId()));
    }

    public function testResetLockedJob() : void
    {
        $job = new BackendTestJob();
        $job->setName('Job 1');
        $queuedJob = $this->pipeline->enqueue($job);

        $job = $this->pipeline->dequeue();
        $this->assertEquals($queuedJob->getId(), $job->getId());
        $this->assertNotNull($this->pipeline->find($queuedJob->getId()));
        $this->assertEquals('locked', $this->pipeline->find($queuedJob->getId())->getStatus());

        $this->assertNull($this->pipeline->dequeue());
        $this->pipeline->reset($job);
        $this->assertNotNull($this->pipeline->find($queuedJob->getId()));
        $this->assertEquals('free', $this->pipeline->find($queuedJob->getId())->getStatus());

        $job = $this->pipeline->dequeue();
        $this->assertEquals($queuedJob->getId(), $job->getId());
        $this->assertNotNull($this->pipeline->find($queuedJob->getId()));
        $this->assertEquals('locked', $this->pipeline->find($queuedJob->getId())->getStatus());
    }
}
