<?php

declare(strict_types=1);

namespace Emphloyer;

use PHPUnit\Framework\TestCase;
use function count;

class JobWithHooks extends AbstractJob
{
    public function beforeFail() : void
    {
    }

    public function beforeComplete() : void
    {
    }

    public function perform() : void
    {
    }
}

class BossTest extends TestCase
{
    public function setUp() : void
    {
        $this->pipeline  = $this->getMockBuilder('Emphloyer\Pipeline')
            ->disableOriginalConstructor()
            ->getMock();
        $this->scheduler = $this->getMockBuilder('Emphloyer\Scheduler')
            ->disableOriginalConstructor()
            ->getMock();
        $this->boss      = new Boss($this->pipeline, $this->scheduler);
    }

    public function testScheduleWorkDoesNothingWhenThereIsNoScheduler() : void
    {
        $boss = new Boss($this->pipeline);
        $this->pipeline->expects($this->never())
            ->method('enqueue');

        $boss->scheduleWork();
    }

    public function testScheduleWorkEnqueuesJobsReturnedByScheduler() : void
    {
        $job1 = $this->createMock('Emphloyer\Job');
        $job2 = $this->createMock('Emphloyer\Job');
        $jobs = [$job1, $job2];

        $this->scheduler->expects($this->once())
            ->method('getJobsFor')
            ->will($this->returnValue($jobs));

        $this->pipeline->expects($this->at(0))
            ->method('enqueue')
            ->with($job1);

        $this->pipeline->expects($this->at(1))
            ->method('enqueue')
            ->with($job2);

        $this->boss->scheduleWork();
    }

    public function testGetEmployees() : void
    {
        $this->boss->allocateEmployee(new Employee());
        $this->boss->allocateEmployee(new Employee());
        $this->boss->allocateEmployee(new Employee());
        $employees = $this->boss->getEmployees();
        $this->assertEquals(3, count($employees));
        foreach ($employees as $employee) {
            $this->assertInstanceOf('Emphloyer\Employee', $employee);
        }
    }

    public function testGetWorkReturnsJobFromPipeline() : void
    {
        $options  = ['only' => ['special']];
        $employee = new Employee($options);
        $job      = $this->createMock('Emphloyer\Job');
        $this->pipeline->expects($this->once())
            ->method('dequeue')
            ->with($options)
            ->will($this->returnValue($job));
        $this->assertEquals($job, $this->boss->getWork($employee));
    }

    public function testGetWorkReturnsNullWhenThereIsNoWork() : void
    {
        $employee = new Employee();
        $this->pipeline->expects($this->once())
            ->method('dequeue')
            ->with([])
            ->will($this->returnValue(null));
        $this->assertNull($this->boss->getWork($employee));
    }

    public function testDelegateWorkDelegatesToAvailableEmployees() : void
    {
        $employee1 = $this->createMock('Emphloyer\Employee');
        $employee2 = $this->createMock('Emphloyer\Employee');
        $employee3 = $this->createMock('Emphloyer\Employee');
        $this->boss->allocateEmployee($employee1);
        $this->boss->allocateEmployee($employee2);

        $employee1->expects($this->any())
            ->method('isFree')
            ->will($this->returnValue(false));

        $job = $this->createMock('Emphloyer\Job');
        $employee2->expects($this->any())
            ->method('isFree')
            ->will($this->returnValue(true));
        $employee2->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue(['only' => ['special']]));
        $employee2->expects($this->once())
            ->method('work')
            ->with($job);

        $this->pipeline->expects($this->once())
            ->method('dequeue')
            ->with(['only' => ['special']])
            ->will($this->returnValue($job));

        $this->pipeline->expects($this->once())
            ->method('reconnect');

        $this->boss->delegateWork();
    }

    public function testDelegateWorkDoesNothingWhenNoEmployeeAvailable() : void
    {
        $employee = $this->createMock('Emphloyer\Employee');
        $this->boss->allocateEmployee($employee);

        $employee->expects($this->any())
            ->method('isFree')
            ->will($this->returnValue(false));

        $this->pipeline->expects($this->never())
            ->method('dequeue');
        $this->boss->delegateWork();
    }

    public function testDelegateWorkDoesNothingWhenNoJobAvailable() : void
    {
        $employee = $this->createMock('Emphloyer\Employee');
        $this->boss->allocateEmployee($employee);

        $employee->expects($this->any())
            ->method('isFree')
            ->will($this->returnValue(true));

        $employee->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue([]));

        $this->pipeline->expects($this->once())
            ->method('dequeue')
            ->with([])
            ->will($this->returnValue(null));

        $employee->expects($this->never())
            ->method('work');

        $this->boss->delegateWork();
    }

    public function testWaitOnEmployees() : void
    {
        $employee1 = $this->createMock('Emphloyer\Employee');
        $employee2 = $this->createMock('Emphloyer\Employee');
        $this->boss->allocateEmployee($employee1);
        $this->boss->allocateEmployee($employee2);

        $employee1->expects($this->once())
            ->method('isBusy')
            ->will($this->returnValue(false));

        $employee2->expects($this->once())
            ->method('isBusy')
            ->will($this->returnValue(true));
        $employee2->expects($this->once())
            ->method('getWorkState')
            ->with(true);

        $this->boss->waitOnEmployees();
    }

    public function testStopEmployees() : void
    {
        $employee1 = $this->createMock('Emphloyer\Employee');
        $employee2 = $this->createMock('Emphloyer\Employee');
        $this->boss->allocateEmployee($employee1);
        $this->boss->allocateEmployee($employee2);

        $employee1->expects($this->once())
            ->method('isBusy')
            ->will($this->returnValue(false));

        $employee2->expects($this->once())
            ->method('isBusy')
            ->will($this->returnValue(true));
        $employee2->expects($this->once())
            ->method('stop');

        $this->boss->stopEmployees();
    }

    public function testUpdateProgressWithFreeEmployee() : void
    {
        $freeEmployee = $this->createMock('Emphloyer\Employee');
        $this->boss->allocateEmployee($freeEmployee);

        $freeEmployee->expects($this->once())
            ->method('isFree')
            ->will($this->returnValue(true));

        $this->boss->updateProgress();
    }

    public function testUpdateProgressWithBusyEmployee() : void
    {
        $busyEmployee = $this->createMock('Emphloyer\Employee');
        $this->boss->allocateEmployee($busyEmployee);

        $busyEmployee->expects($this->once())
            ->method('isBusy')
            ->will($this->returnValue(true));

        $this->boss->updateProgress();
    }

    public function testUpdateProgressWithCompletedEmployee() : void
    {
        $completedEmployee = $this->createMock('Emphloyer\Employee');
        $this->boss->allocateEmployee($completedEmployee);

        $completedEmployee->expects($this->once())
            ->method('isFree')
            ->will($this->returnValue(false));
        $completedEmployee->expects($this->once())
            ->method('isBusy')
            ->will($this->returnValue(false));
        $completedEmployee->expects($this->once())
            ->method('getWorkState')
            ->will($this->returnValue(Employee::COMPLETE));
        $completedJob = $this->createMock('Emphloyer\Job');
        $completedEmployee->expects($this->once())
            ->method('getJob')
            ->will($this->returnValue($completedJob));
        $this->pipeline->expects($this->once())
            ->method('complete')
            ->with($completedJob);
        $completedEmployee->expects($this->once())
            ->method('free');

        $this->boss->updateProgress();
    }

    public function testUpdateProgressWithCompletedEmployeeWithJobThatHasHook() : void
    {
        $completedEmployee = $this->createMock('Emphloyer\Employee');
        $this->boss->allocateEmployee($completedEmployee);

        $completedEmployee->expects($this->once())
            ->method('isFree')
            ->will($this->returnValue(false));
        $completedEmployee->expects($this->once())
            ->method('isBusy')
            ->will($this->returnValue(false));
        $completedEmployee->expects($this->once())
            ->method('getWorkState')
            ->will($this->returnValue(Employee::COMPLETE));
        $completedJob = $this->createMock('Emphloyer\JobWithHooks');
        $completedEmployee->expects($this->once())
            ->method('getJob')
            ->will($this->returnValue($completedJob));
        $completedJob->expects($this->once())
            ->method('beforeComplete');
        $this->pipeline->expects($this->once())
            ->method('complete')
            ->with($completedJob);
        $completedEmployee->expects($this->once())
            ->method('free');

        $this->boss->updateProgress();
    }

    public function testUpdateProgressWithFailedJobThatMayNotBeRetried() : void
    {
        $failedEmployee = $this->createMock('Emphloyer\Employee');
        $this->boss->allocateEmployee($failedEmployee);

        $failedEmployee->expects($this->once())
            ->method('isFree')
            ->will($this->returnValue(false));
        $failedEmployee->expects($this->once())
            ->method('isBusy')
            ->will($this->returnValue(false));
        $failedEmployee->expects($this->once())
            ->method('getWorkState')
            ->will($this->returnValue(Employee::FAILED));
        $failedJob = $this->createMock('Emphloyer\Job');
        $failedEmployee->expects($this->once())
            ->method('getJob')
            ->will($this->returnValue($failedJob));
        $this->pipeline->expects($this->once())
            ->method('fail')
            ->with($failedJob);
        $failedEmployee->expects($this->once())
            ->method('free');

        $this->boss->updateProgress();
    }

    public function testUpdateProgressWithFailedJobThatMayBeRetried() : void
    {
        $failedEmployeeWithRetryableJob = $this->createMock('Emphloyer\Employee');
        $this->boss->allocateEmployee($failedEmployeeWithRetryableJob);

        $failedEmployeeWithRetryableJob->expects($this->once())
            ->method('isFree')
            ->will($this->returnValue(false));
        $failedEmployeeWithRetryableJob->expects($this->once())
            ->method('isBusy')
            ->will($this->returnValue(false));
        $failedEmployeeWithRetryableJob->expects($this->once())
            ->method('getWorkState')
            ->will($this->returnValue(Employee::FAILED));
        $retryableJob = $this->createMock('Emphloyer\Job');
        $failedEmployeeWithRetryableJob->expects($this->once())
            ->method('getJob')
            ->will($this->returnValue($retryableJob));
        $retryableJob->expects($this->once())
            ->method('mayTryAgain')
            ->will($this->returnValue(true));
        $this->pipeline->expects($this->once())
            ->method('reset')
            ->with($retryableJob);
        $failedEmployeeWithRetryableJob->expects($this->once())
            ->method('free');

        $this->boss->updateProgress();
    }

    public function testUpdateProgressWithFailedJobThatHasOnFailHook() : void
    {
        $failedEmployeeWithRetryableJob = $this->createMock('Emphloyer\Employee');
        $this->boss->allocateEmployee($failedEmployeeWithRetryableJob);

        $failedEmployeeWithRetryableJob->expects($this->once())
            ->method('isFree')
            ->will($this->returnValue(false));
        $failedEmployeeWithRetryableJob->expects($this->once())
            ->method('isBusy')
            ->will($this->returnValue(false));
        $failedEmployeeWithRetryableJob->expects($this->once())
            ->method('getWorkState')
            ->will($this->returnValue(Employee::FAILED));
        $retryableJob = $this->createMock('Emphloyer\JobWithHooks');
        $failedEmployeeWithRetryableJob->expects($this->once())
            ->method('getJob')
            ->will($this->returnValue($retryableJob));
        $retryableJob->expects($this->once())
            ->method('beforeFail');
        $retryableJob->expects($this->once())
            ->method('mayTryAgain')
            ->will($this->returnValue(true));
        $this->pipeline->expects($this->once())
            ->method('reset')
            ->with($retryableJob);
        $failedEmployeeWithRetryableJob->expects($this->once())
            ->method('free');

        $this->boss->updateProgress();
    }
}
