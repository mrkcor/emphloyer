<?php

declare(strict_types=1);

namespace Emphloyer;

use PHPUnit\Framework\TestCase;
use function count;

class WorkshopTest extends TestCase
{
    public function setUp() : void
    {
        $this->boss     = $this->getMockBuilder('Emphloyer\Boss')
            ->disableOriginalConstructor()
            ->getMock();
        $this->workshop = new Workshop($this->boss, [['employees' => 2]]);
    }

    public function testConstructor() : void
    {
        $pipeline  = $this->getMockBuilder('Emphloyer\Pipeline')->disableOriginalConstructor()->getMock();
        $scheduler = $this->getMockBuilder('Emphloyer\Scheduler')->disableOriginalConstructor()->getMock();
        $boss      = new Boss($pipeline, $scheduler);
        $workshop  = new Workshop(
            $boss,
            [['employees' => 2], ['employees' => 1, 'only' => ['special']]]
        );

        $employees = $boss->getEmployees();
        $this->assertEquals(3, count($employees));

        $countRegular = 0;
        $countSpecial = 0;
        foreach ($employees as $employee) {
            $this->assertInstanceOf('Emphloyer\Employee', $employee);
            $options = $employee->getOptions();
            if (isset($options['only'])) {
                $this->assertEquals(['employees' => 1, 'only' => ['special']], $options);
                $countSpecial += 1;
            } else {
                $countRegular += 1;
            }
        }

        $this->assertEquals(1, $countSpecial);
        $this->assertEquals(2, $countRegular);
    }

    public function testRun() : void
    {
        $this->boss->expects($this->once())
            ->method('scheduleWork');
        $this->boss->expects($this->once())
            ->method('delegateWork');
        $this->boss->expects($this->exactly(2))
            ->method('updateProgress');
        $this->boss->expects($this->once())
            ->method('waitOnEmployees');

        $this->workshop->run(false);
    }

    public function testStopNow() : void
    {
        $this->boss->expects($this->once())
            ->method('stopEmployees');
        $this->boss->expects($this->once())
            ->method('updateProgress');

        $this->workshop->stopNow();
    }
}
