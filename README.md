# Emphloyer

There comes a time in the life of a PHP application that async job processing
becomes a requirement. If you want something flexible that you can easily adapt
to fit in with your application's infrastucture, then Emphloyer may be what you
are looking for.

## Installation

You can install Emphloyer through composer with:

    composer require mkrmr/emphloyer

## Usage

To use Emphloyer to run jobs you need to:

- Define your own jobs in classes that implement the \Emphloyer\Job interface,
  you can extend the \Emphloyer\AbstractJob if you like. 
- Hook up Emphloyer with a backend to manage queue of jobs.

### Defining your own jobs

Implementing your own jobs is simple, here's a silly example:

```php
class NameEchoJob extends \Emphloyer\AbstractJob {
   public function setName($name) {
      $this->attributes['name'] = $name;
   }

   public function perform() {
      echo "Hi, my name is {$this->attributes['name']}.\n";
   }

   public function mayTryAgain() {
      return false;
   }
}
```

Anything in the attributes property will get serialized when the job gets queued
up. 

The perform method is what is executed when Emphloyer runs the job.

When a job fails the mayTryAgain method determines whether it may be attempted
again or not. 

### Hooking up a backend

Emphloyer manages its jobs through its pipeline, in order to feed jobs into the
pipeline and to get jobs out of it you need to connect a backend to it. You can
either use a backend that someone has built already (such as Emphloyer-PDO) or
implement your own. To build your own backend you must implement the
\Emphloyer\Pipeline\Backend interface.

To enable Emphloyer to run with the backend of your choice you need to create a
configuration file that you reference at runtime, here's an annotated example:

```php
<?php
// $pipelineBackend defines the pipeline backend to use
$pipelineBackend = new \Emphloyer\Pdo\PipelineBackend("mysql:dbname=emphloyer_example;host=localhost", "user", "password");
// $numberOfEmployees determines the number of concurrent jobs to run, each job is forked off using pcntl_fork
$numberOfEmployees = 4;
?>
```

After setting your configuration file you can execute Emphloyer like so:

    /path/to/project/vendor/bin/emphloyer -c /path/to/config_file.php

If you want to clear the Pipeline of jobs you can append the above command with
--clear. 

To enqueue jobs in your application code you need to instantiate a Pipeline with
the appropriate backend as is done in the configuration file, you can then
simply enqueue jobs by passing an instance to the enqueue method:

```php
$pipelineBackend = new \Emphloyer\Pdo\PipelineBackend("mysql:dbname=emphloyer_example;host=localhost", "user", "password");
$pipelineBackend->enqueue($job);
```

## Contributing

1. Fork it
2. Create your feature branch (`git checkout -b my-new-feature`)
3. Commit your changes (`git commit -am 'Add some feature'`)
4. Push to the branch (`git push origin my-new-feature`)
5. Create new Pull Request

