<?php

namespace Emphloyer;

class WorkshopTest extends \PHPUnit_Framework_TestCase {
  public function setUp() {
    $this->boss = $this->getMockBuilder('Emphloyer\Boss')
      ->disableOriginalConstructor()
      ->getMock();
    $this->workshop = new Workshop($this->boss, $this->getMock('Emphloyer\Job\ForkHookChain'), 2);
  }

  public function testConstructor() {
    $forkHooks = $this->getMock('Emphloyer\Job\ForkHookChain');
    $boss = new Boss($this->getMockBuilder('Emphloyer\Pipeline')->disableOriginalConstructor()->getMock());
    $workshop = new Workshop($boss, $forkHooks, 2);

    $employees = $boss->getEmployees();
    $this->assertEquals(2, count($employees));

    $this->assertSame($forkHooks, $employees[0]->getForkHooks());
    $this->assertSame($forkHooks, $employees[1]->getForkHooks());
  }

  public function testRun() {
    $this->boss->expects($this->once())
      ->method('delegateWork');
    $this->boss->expects($this->once())
      ->method('updateProgress');

    $this->workshop->run(false);
  }

  public function testStop() {
    $this->boss->expects($this->once())
      ->method('waitOnEmployees');
    $this->boss->expects($this->once())
      ->method('updateProgress');

    $this->workshop->stop();
  }

  public function testStopNow() {
    $this->boss->expects($this->once())
      ->method('stopEmployees');
    $this->boss->expects($this->once())
      ->method('updateProgress');

    $this->workshop->stopNow();
  }
}
