<?php

declare(strict_types=1);

namespace Emphloyer;

use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use function escapeshellarg;
use function is_dir;
use function mkdir;
use function posix_kill;
use function system;
use function usleep;

class EmployeeTest extends TestCase
{
    public function setUp() : void
    {
        $this->employee = new Employee();

        $this->tempPath = __DIR__ . '/_files/tmp';

        if (is_dir($this->tempPath)) {
            system('rm -rf ' . escapeshellarg($this->tempPath));
        }

        mkdir($this->tempPath);
    }

    public function testValidOptions() : void
    {
        $options  = ['only' => ['special']];
        $employee = new Employee($options);
        $this->assertEquals($options, $employee->getOptions());

        $options  = ['exclude' => ['special']];
        $employee = new Employee($options);
        $this->assertEquals($options, $employee->getOptions());
    }

    public function testInvalidOptionsThrowsException() : void
    {
        $invalids = [
            ['only' => 'special'],
            ['exclude' => 'special'],
        ];

        $fails = 0;
        foreach ($invalids as $options) {
            try {
                $employee = new Employee($options);
                $this->fail('InvalidArgumentException was expected.');
            } catch (InvalidArgumentException $e) {
                ++$fails;
            }
        }
        $this->assertEquals(2, $fails);
    }

    public function shortSleep() : void
    {
        usleep(100000);
    }

    public function shortSleepAndFail() : void
    {
        usleep(100000);
        throw new Exception();
    }

    public function testIsFree() : void
    {
        $this->assertTrue($this->employee->isFree());
        $this->employee->work($this->createMock('Emphloyer\Job'));
        $this->assertFalse($this->employee->isFree());
    }

    public function testGetJob() : void
    {
        $this->assertNull($this->employee->getJob());

        $job = $this->createMock('Emphloyer\Job');
        $this->employee->work($job);
        $this->assertEquals($job, $this->employee->getJob());
    }

    public function testRejectsJobWhileItIsNotFree() : void
    {
        $this->employee->work($this->getCompletingJob());
        $this->assertFalse($this->employee->isFree());

        try {
            $this->employee->work($this->getCompletingJob());
            $this->fail('Expected an EmployeeNotFreeException');
        } catch (Exceptions\EmployeeNotFree $e) {
        }
    }

    public function getCompletingJob()
    {
        $job = $this->createMock('Emphloyer\Job');
        $job->expects($this->any())
            ->method('perform')
            ->will($this->returnCallback([$this, 'shortSleep']));

        return $job;
    }

    public function testCannotBeFreedUntilJobIsCompletedOrFailed() : void
    {
        $this->employee->work($this->getCompletingJob());
        $this->assertFalse($this->employee->isFree());

        try {
            $this->employee->free();
            $this->fail("Shouldn't be able to free a busy employee");
        } catch (Exceptions\EmployeeIsBusy $exception) {
        }

        usleep(200000);
        $this->employee->free();
        $this->assertTrue($this->employee->isFree());

        $this->employee->work($this->getFailingJob());
        $this->assertFalse($this->employee->isFree());

        try {
            $this->employee->free();
            $this->fail("Shouldn't be able to free a busy employee");
        } catch (Exceptions\EmployeeIsBusy $exception) {
        }

        usleep(200000);
        $this->employee->free();
        $this->assertTrue($this->employee->isFree());
    }

    public function getFailingJob()
    {
        $job = $this->createMock('Emphloyer\Job');
        $job->expects($this->any())
            ->method('perform')
            ->will($this->returnCallback([$this, 'shortSleepAndFail']));

        return $job;
    }

    public function testReportsWorkState() : void
    {
        $this->employee->work($this->getCompletingJob());
        $this->assertEquals(Employee::BUSY, $this->employee->getWorkState());
        usleep(200000);
        $this->assertEquals(Employee::COMPLETE, $this->employee->getWorkState());
        $this->employee->free();

        $this->employee->work($this->getFailingJob());
        $this->assertEquals(Employee::BUSY, $this->employee->getWorkState());
        usleep(200000);
        $this->assertEquals(Employee::FAILED, $this->employee->getWorkState());
    }

    public function testReportWorkStateAndWaitForCompletion() : void
    {
        $this->employee->work($this->getCompletingJob());
        $this->assertEquals(Employee::BUSY, $this->employee->getWorkState());
        $this->assertEquals(Employee::COMPLETE, $this->employee->getWorkState(true));
    }

    public function testStopEmployee() : void
    {
        $this->employee->work($this->getCompletingJob());
        $this->assertTrue($this->employee->isBusy());
        $workPid = $this->employee->getWorkPid();
        $this->assertNotNull($workPid);

        $this->employee->stop();
        $this->assertFalse(posix_kill($workPid, 0));
        $this->assertFalse($this->employee->isBusy());
        $this->assertEquals(Employee::FAILED, $this->employee->getWorkState());
    }
}
