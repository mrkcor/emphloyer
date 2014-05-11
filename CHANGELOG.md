## 0.3.0 (unreleased)

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
