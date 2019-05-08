# Emphloyer

There comes a time in the life of a PHP application that async job processing
becomes a requirement. If you want something flexible that you can easily adapt
to fit in with your application's infrastucture, then Emphloyer may be what you
are looking for.

## Installation

You can install Emphloyer through composer with:

    composer require mkrmr/emphloyer

## Usage

Using Emphloyer is pretty simple, in your application code you queue up jobs and
running Emphloyer's command line program processes the queue.

Before you can start using Emphloyer you need to:

- Define your own jobs in classes that implement the \Emphloyer\Job interface,
  you can extend the \Emphloyer\AbstractJob if you like. 
- Hook up Emphloyer with a backend to manage the queueing of jobs.

Additionally you can use Emphloyer to schedule jobs like you would in the
crontab, to do so you will need to hook up the scheduler with a backend as well.

### Defining your own jobs

Here's a silly example of a job impementation:

```php
use Emphloyer\AbstractJob

class NameEchoJob extends AbstractJob 
{
   public function setName(string $name) : void 
   {
      $this->attributes['name'] = $name;
   }

   public function perform() : void 
   {
      echo "Hi, my name is {$this->attributes['name']}.\n";
   }
}
```

Anything in the attributes array will get serialized when the job gets queued
up. Note that the keys 'className' and 'type' are reserved and should not be
used in your own job implementations. 

The perform method is what is executed when Emphloyer runs the job, if this
raises an exception then the job will fail. 

When a job fails the mayTryAgain method determines whether it may be attempted
again or not. You could for example implement retry behaviour by keeping track
of the number of retries in your job's internal attributes. Note that changing
the attributes during the perform method will not persist them in the backend
because the perform method is executed in a forked process and never
communicated back to the master process, instead implement the beforeFail hook
in your job instance and have that update the attributes in the job's instance 
in the master process.

You can control the number of processes for jobs based on their type. When you
inherit from the \Emphloyer\AbstractJob class the type will be set to 'job' by
default, you can use the setType method to set a specific type on an instance
before you enqueue it or you can override the $type instance variable in your
class to set another default.

### Hooking up a backend 

Emphloyer manages its jobs through its pipeline, in order to feed jobs into the
pipeline and to get jobs out of it you need to connect a backend to it. You can
either use a backend that someone has built already (such as [Emphloyer-PDO](https://github.com/mkremer/emphloyer-pdo)) or
implement your own. To build your own backend you must implement the
\Emphloyer\Pipeline\Backend interface.

To enable Emphloyer to run with the backend of your choice you need to create a
configuration file that you reference at runtime, here's an annotated example:

```php
<?php
// $pipelineBackend defines the pipeline backend to use
$pipelineBackend = new \Emphloyer\Pdo\PipelineBackend('mysql:dbname=emphloyer_example;host=localhost', 'user', 'password');
// $employees determines the number of concurrent jobs, each job is forked off using pcntl_fork. Each entry is used, so if you specify duplicates that will simply add more employees for those types.
$employees = [
   ['exclude' => ['reports'], 'employees' => 2], // fork up to two processes for jobs of any type except 'reports'
   ['only' => ['reports', 'stuff'], 'employees' => 1], // fork up to one process for jobs of the types 'reports' and 'stuff'
   ['employees' => 4], // fork up to four processes for jobs of any type
];
```

After setting your configuration file you can have Emphloyer process jobs like 
so:

    /path/to/project/vendor/bin/emphloyer -c /path/to/config_file.php

If you want to clear the Pipeline of jobs you can add --clear to the above command.

To enqueue jobs in your application code you need to instantiate a Pipeline with
the appropriate backend as is done in the configuration file, you can then
simply enqueue jobs by passing an instance to the enqueue method:

```php
$pipelineBackend = new \Emphloyer\Pdo\PipelineBackend('mysql:dbname=emphloyer_example;host=localhost', 'user', 'password');
$pipeline = new \Emphloyer\Pipeline($pipelineBackend);
$queuedJob = $pipeline->enqueue($job);
```

As you can see from the above snippet the enqueue method returns a job object,
this is a new instance loaded with the attributes as returned by the backend's
enqueue method. The backend should include a unique id attribute that can be 
used to identify the job (like the Employer-PDO backend does), this can be
useful if you want to poll whether a job you queued up has been completed. 

The AbstractJob class assumes this attribute is stored as the id field in the 
attributes array and provides the getId method to access it.  To check on a job 
you can use the Pipeline's find method with the job id to load it, depending on 
the backend completed jobs may no longer be stored in which case that method 
will return null (the Employer-PDO backend will delete completed jobs from the 
database for example). 

Besides using the pipeline you can also use the scheduler to run specific jobs
at set intervals like you would in the crontab. To do so you will have to hookup
the scheduler to a backend in the same configuration file where you setup the
pipeline like so: 

```php
// $schedulerBackend defines the scheduler backend to use
$schedulerBackend = new \Emphloyer\Pdo\SchedulerBackend('mysql:dbname=emphloyer_example;host=localhost', 'user', 'password');
```

As with the Pipeline you can either use a backend that someone has built \
already (such as [Emphloyer-PDO](https://github.com/mkremer/emphloyer-pdo) which
will soon have a backend for the Scheduler) or implement your own. To build 
your own backend you must implement the \Emphloyer\Scheduler\Backend interface.

If you have followed along this README and started Emphloyer earlier you will
have to stop and start it to start using the scheduler.

To schedule jobs you need to instantiate a Scheduler with the appropriate
backend as is done in the configuration file, you can then schedule jobs by
passing an instance of said job to the schedule method:

```php
$schedulerBackend = new \Emphloyer\Pdo\SchedulerBackend('mysql:dbname=emphloyer_example;host=localhost', 'user', 'password');
$scheduler = new \Emphloyer\Scheduler($schedulerBackend);
// Arguments after the job follow the crontab syntax: minute, hour, day of month, month, day of week
$scheduler->schedule($job, 30, 12); // Schedules the job to be enqueued every day at 12:30
```

You can get insight into the schedule by using the allEntries method on the
Scheduler which returns an iterator that returns ScheduleEntry objects. In
addition to this the delete method can be used to delete a specific entry from
the schedule.

## Contributing

#. Fork it
#. Create your feature branch (`git checkout -b my-new-feature`)
#. Make your changes, please make sure you adhere to the Doctrine coding standard as much as possible (phpcs configuration is included)
#. Commit your changes (`git commit -am 'Add some feature'`)
#. Push to the branch (`git push origin my-new-feature`)
#. Create a new pull request on GitHub

