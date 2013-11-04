<?php

namespace Emphloyer\Job;

class ForkHookChainTest extends \PHPUnit_Framework_TestCase {
  public function setUp() {
    $this->chain = new ForkHookChain();
  }

  public function testRun() {
    $job = $this->getMock('Emphloyer\Job');
    $hook1 = $this->getMock('Emphloyer\Job\ForkHook');
    $hook2 = $this->getMock('Emphloyer\Job\ForkHook');
    $hook3 = $this->getMock('Emphloyer\Job\ForkHook');

    $this->chain->add($hook1);
    $this->chain->add($hook2);

    $hook1->expects($this->once())
      ->method('run')
      ->with($job);
    $hook2->expects($this->once())
      ->method('run')
      ->with($job);
    $hook3->expects($this->never())
      ->method('run');

    $this->chain->run($job);
  }
}
