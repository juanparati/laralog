Laralog
=======

1. What is it?
--------------

An ElasticSearch log sender for Laravel developed in PHP that can replaces Logstash.

Laralog is suitable for docker containers and small Laravel or Lumen environments that requires to collect and send logs without install Logstash and Java runtime.

Laralog is decoupled from Laravel, so it can read and send the Laravel logs even when Laravel fails.




2. Build
--------

Laralog is available as self-executable PHAR file, however if you want to build your own custom PHAR you need the follow this steps:


* [Download caveman](https://github.com/Mamuph/caveman/releases) (The Mamuph Helper Tool)
* Inside the Laralog directory type:

        caveman build . -x -r


       