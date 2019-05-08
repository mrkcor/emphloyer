<?php

declare(strict_types=1);

namespace Emphloyer;

use PHPUnit\Framework\TestCase;

class PipelineTestJob extends AbstractJob
{
    public function __construct(string $name = '', bool $again = false)
    {
        $this->attributes['name']      = $name;
        $this->attributes['try_again'] = $again;
    }

    public function mayTryAgain() : bool
    {
        return $this->attributes['try_again'] === true;
    }

    public function perform() : void
    {
    }
}

class PipelineTest extends TestCase
{
    public function setUp() : void
    {
        $this->backend  = $this->createMock('\Emphloyer\Pipeline\Backend');
        $this->pipeline = new Pipeline($this->backend);
    }

    public function testReconnect() : void
    {
        $this->backend->expects($this->once())
            ->method('reconnect');
        $this->pipeline->reconnect();
    }

    public function testEnqueueSerializesAndPassesToBackend() : void
    {
        $job = new PipelineTestJob('Mark');
        $this->backend->expects($this->once())
            ->method('enqueue')
            ->with(
                $this->equalTo(
                    [
                        'className' => 'Emphloyer\PipelineTestJob',
                        'name' => 'Mark',
                        'try_again' => false,
                        'type' => 'job',
                    ]
                )
            )
            ->will(
                $this->returnValue(
                    [
                        'className' => 'Emphloyer\PipelineTestJob',
                        'name' => 'Mark',
                        'try_again' => false,
                        'id' => 1,
                        'type' => 'job',
                    ]
                )
            );

        $savedJob = $this->pipeline->enqueue($job);
        $this->assertInstanceOf('Emphloyer\PipelineTestJob', $savedJob);
        $this->assertEquals(1, $savedJob->getId());
    }

    public function testFindInstantiatesJobFromBackendAttributes() : void
    {
        $this->backend->expects($this->once())
            ->method('find')
            ->with(2)
            ->will(
                $this->returnValue(
                    [
                        'className' => 'Emphloyer\PipelineTestJob',
                        'name' => 'Kremer',
                        'try_again' => false,
                        'id' => 2,
                        'type' => 'special',
                    ]
                )
            );
        $job = $this->pipeline->find(2);
        $this->assertInstanceOf('Emphloyer\PipelineTestJob', $job);
        $this->assertEquals(['name' => 'Kremer', 'try_again' => false, 'id' => 2], $job->getAttributes());
        $this->assertEquals('special', $job->getType());
    }

    public function testFindReturnsNullWhenBackendReturnsNull() : void
    {
        $this->backend->expects($this->once())
            ->method('find')
            ->with(3)
            ->will($this->returnValue(null));
        $job = $this->pipeline->find(3);
        $this->assertNull($job);
    }

    public function testDequeueInstantiatesJobFromBackendAttributes() : void
    {
        $this->backend->expects($this->once())
            ->method('dequeue')
            ->will(
                $this->returnValue(
                    [
                        'className' => 'Emphloyer\PipelineTestJob',
                        'name' => 'Mark',
                        'try_again' => false,
                        'type' => 'x',
                    ]
                )
            );
        $job = $this->pipeline->dequeue();
        $this->assertInstanceOf('Emphloyer\PipelineTestJob', $job);
        $this->assertEquals(['name' => 'Mark', 'try_again' => false], $job->getAttributes());
        $this->assertEquals('x', $job->getType());
    }

    public function testDequeueReturnsNullWhenBackendReturnsNull() : void
    {
        $this->backend->expects($this->once())
            ->method('dequeue')
            ->will($this->returnValue(null));
        $job = $this->pipeline->dequeue();
        $this->assertNull($job);
    }

    public function testReset() : void
    {
        $job = new PipelineTestJob('Mark');
        $this->backend->expects($this->once())
            ->method('reset')
            ->with(
                $this->equalTo(
                    [
                        'className' => 'Emphloyer\PipelineTestJob',
                        'name' => 'Mark',
                        'try_again' => false,
                        'type' => 'job',
                    ]
                )
            );
        $this->pipeline->reset($job);
    }

    public function testComplete() : void
    {
        $job = new PipelineTestJob('Mark');
        $this->backend->expects($this->once())
            ->method('complete')
            ->with(
                $this->equalTo(
                    [
                        'className' => 'Emphloyer\PipelineTestJob',
                        'name' => 'Mark',
                        'try_again' => false,
                        'type' => 'job',
                    ]
                )
            );
        $this->pipeline->complete($job);
    }

    public function testFail() : void
    {
        $job = new PipelineTestJob('Mark');
        $this->backend->expects($this->once())
            ->method('fail')
            ->with(
                $this->equalTo(
                    [
                        'className' => 'Emphloyer\PipelineTestJob',
                        'name' => 'Mark',
                        'try_again' => false,
                        'type' => 'job',
                    ]
                )
            );
        $this->pipeline->fail($job);
    }

    public function testClearClearsTheBackend() : void
    {
        $this->backend->expects($this->once())
            ->method('clear');
        $this->pipeline->clear();
    }
}
