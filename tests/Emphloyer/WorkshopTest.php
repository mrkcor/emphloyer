<?php

namespace Emphloyer;

class WorkshopTest extends \PHPUnit_Framework_TestCase {
  public function setUp() {
    $this->boss = $this->getMockBuilder('Emphloyer\Boss')
      ->disableOriginalConstructor()
      ->getMock();
    $this->workshop = new Workshop($this->boss, array(array('employees' => 2)));
  }

  public function testConstructor() {
    $boss = new Boss($this->getMockBuilder('Emphloyer\Pipeline')->disableOriginalConstructor()->getMock());
    $workshop = new Workshop($boss, array(array('employees' => 2), array('employees' => 1, 'only' => array('special'))));

    $employees = $boss->getEmployees();
    $this->assertEquals(3, count($employees));

    $countRegular = 0;
    $countSpecial = 0;
    foreach ($employees as $employee) {
      $this->assertInstanceOf('Emphloyer\Employee', $employee);
      $options = $employee->getOptions();
      if (isset($options['only'])) {
        $this->assertEquals(array('employees' => 1, 'only' => array('special')), $options);
        $countSpecial += 1;
      } else {
        $countRegular += 1;
      }
    }

    $this->assertEquals(1, $countSpecial);
    $this->assertEquals(2, $countRegular);
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
