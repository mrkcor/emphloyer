<?php

declare(strict_types=1);

namespace Emphloyer;

use PHPUnit\Framework\TestCase;

class SerDesTestJob extends AbstractJob
{
    public function setName($name) : void
    {
        $this->attributes['name'] = $name;
    }

    public function getName()
    {
        return $this->attributes['name'];
    }

    public function perform() : void
    {
    }
}

class JobSerDesTest extends TestCase
{
    public function testSerializeJob() : void
    {
        $serDes = new JobSerDes();

        $job = new SerDesTestJob();
        $job->setName('Job 1');
        $job->setType('test');

        $expected = ['name' => 'Job 1', 'type' => 'test', 'className' => 'Emphloyer\SerDesTestJob'];
        $this->assertEquals($serDes->serialize($job), $expected);
    }

    public function testDeserializeJob() : void
    {
        $serDes     = new JobSerDes();
        $serialized = ['name' => 'Job 1', 'type' => 'test', 'className' => 'Emphloyer\SerDesTestJob'];

        $job = $serDes->deserialize($serialized);
        $this->assertInstanceOf('Emphloyer\SerDesTestJob', $job);
        $this->assertEquals('test', $job->getType());
        $this->assertEquals('Job 1', $job->getName());
    }
}
