## 0.4.2 (November 27, 2014)

  - Adds optional beforeFail and beforeComplete hooks to jobs, this can fore
    example be used to steer retries.

## 0.4.1 (September 28, 2014)

  - Fix warning in AbstractJob getId method (for when the id is not set).
  - Switch to the official Ubuntu 12.04 Vagrant box

## 0.4.0 (May 18, 2014)

  - Emphloyer\Scheduler now has allEntries, find and delete method. You can use
    these to manage the schedule.

## 0.3.1 (May 14, 2014)

  - Emphloyer\Pipeline\BackendTestCase modified to be more generically applyable
    to other types of backends.

## 0.3.0 (May 13, 2014)

  - BACKWARDS INCOMPATIBLE CHANGE: Backend::enqueue method must accept an
    optional DateTime parameter to indicate from which date and time in the
    future the job may be executed.
  - Adds scheduler component which allows you to have jobs execute at set
    intervals like in the *nix crontab. The scheduler pushes jobs into the
    pipeline as you have scheduled.

## 0.2.0 (May 11, 2014)

  - BACKWARDS INCOMPATIBLE CHANGE: $numberOfEmployees configuration setting
    replaced with $employees array
  - Allow setting how many processes to spawn for specific types of jobs

## 0.1.2 (November 27, 2013)

  - Correct handling of the CLI stop, prevent new jobs from being distributed.
  - Accept the TERM signal to stop processing as well as the INT signal.

## 0.1.1 (November 11, 2013)

  - Do not redeclare perform method on AbstractJob to allow Emphloyer to run on
    PHP 5.3.3

## 0.1.0 (November 7, 2013)

Initial release.
