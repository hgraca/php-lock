# Lock library
[![Build Status](https://travis-ci.org/hgraca/php-lock.svg?branch=master)](https://travis-ci.org/hgraca/php-lock)
[![codecov](https://codecov.io/gh/hgraca/php-lock/branch/master/graph/badge.svg)](https://codecov.io/gh/hgraca/php-lock)

A generic PHP library to control concurrent access to resources.

## Installation

To install the library, run the command below and you will get the latest version:

```
composer require hgraca/lock
```

## Todo

 - Use FileSystem lib:
    - Create our file system interface
    - Create an adapter for the FileSystem lib, implementing our interface
    - Use our interface in the Lock, with an optional dependency being the FileSystemAdapter
 - Create tests
