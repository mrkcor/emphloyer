<?php

namespace Emphloyer;

class WorkshopTest extends \PHPUnit_Framework_TestCase {
  public function setUp() {
    $this->boss = $this->getMockBuilder('Emphloyer\Boss')
      ->disableOriginalConstructor()
      ->getMock();
    $this->workshop = new Workshop($this->boss, 2);
  }

  public function testConstructor() {
    $boss = new Boss($this->getMockBuilder('Emphloyer\Pipeline')->disableOriginalConstructor()->getMock());
    $workshop = new Workshop($boss, 2);

    $employees = $boss->getEmployees();
    $this->assertEquals(2, count($employees));
  }

  public function testRun() {
    $this->boss->expects($this->once())
      ->method('delegateWork');
    $this->boss->expects($this->exactly(2))
      ->method('updateProgress');
    $this->boss->expects($this->once())
      ->method('waitOnEmployees');

    $this->workshop->run(false);
  }

  public function testStopNow() {
    $this->boss->expects($this->once())
      ->method('stopEmployees');
    $this->boss->expects($this->once())
      ->method('updateProgress');

    $this->workshop->stopNow();
  }
}
