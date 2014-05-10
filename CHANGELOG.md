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
