<?php

namespace Emphloyer;

class SerDesTestJob extends \Emphloyer\AbstractJob
{
    public function setName($name)
    {
        $this->attributes['name'] = $name;
    }

    public function getName()
    {
        return $this->attributes['name'];
    }

    public function perform()
    {
    }
}

class JobSerDesTest extends \PHPUnit\Framework\TestCase
{
    public function testSerializeJob()
    {
        $serDes = new JobSerDes();

        $job = new SerDesTestJob();
        $job->setName('Job 1');
        $job->setType('test');

        $expected = array("name" => "Job 1", "type" => "test", "className" => "Emphloyer\SerDesTestJob");
        $this->assertEquals($serDes->serialize($job), $expected);
    }

    public function testDeserializeJob()
    {
        $serDes = new JobSerDes();
        $serialized = array("name" => "Job 1", "type" => "test", "className" => "Emphloyer\SerDesTestJob");

        $job = $serDes->deserialize($serialized);
        $this->assertInstanceOf("Emphloyer\SerDesTestJob", $job);
        $this->assertEquals("test", $job->getType());
        $this->assertEquals("Job 1", $job->getName());
    }
}
