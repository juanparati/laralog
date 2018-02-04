Laralog
=======

## 1. What is it?

An Elastic Search log sender for Laravel.

Laralog is suitable for docker containers and small Laravel or Lumen environments that requires to collect and send logs without install the Logstash and the Java runtime.

Laralog is decoupled from Laravel, so it can read and send the Laravel logs even when Laravel fails.


## 2. Features

- PHP based solution.
- Distributed as a single PHAR file.
- Compatible with Supervisor.
- Small footprint.
- Easy to configure.
- File handler safe (Logs files can be rotated, linked or truncated on fly).
- Decoupled solution based on [Mamuph framework](http://www.mamuph.org)


## 3. How it works?

- Read logs sequentially without stop and send logs to a single ES instance using a custom index:

        laralog https://myesinstance.lan:9200 --input=laravel.log --index=myindex
        
- Read logs from a external file and logs to a group of server defined in a file (One instance per line):

        laralog my_es_servers.txt < laravel.log

- Read logs sequentially without stop and send logs asynchronously using a different timezone:

        laralog https://myinstance.lan:9200 --async --timezone=Europe/Madrid

- Read logs sequentially without stop and output the new entries to STDOUT:

        laralog https://myinstance.lan:9200 -v
        

## 4. Parameters

|Parameter|Description|
|--------|----------|
|--input=[laravel.log]|Log file path to read sequentially without stop. When this parameter is not used Laralog will read from the STDIN.|
|--index=[index_name]|Document index used by Elastic Search.|
|--ignore=[levels]|Error levels to ignore separated by comma.|
|--batch_size=[size]|Batch size used by queue. By default the batch size is 100.|
|--async|Send logs asynchronous using the future mode (Faster approach).|
|--no-check-cert|Do not check the SSL certificate when https is used.|
|--retries=[number]|Number of attemps when logs a sent without success. By default 2 are the number of retries when this parameter is not used.|
|--read-freq=[ms]|File read frequency in milliseconds. 500ms (0.5 sec) is the default value.|
|--timezone=[zone]|Convert Laravel logs timestamps to different timezones. See [timezone list](http://php.net/manual/en/timezones.php).|
|-v|Verbose mode that output the new log entries to STDOUT.| 


## 5. Build

Laralog is available as a self-executable PHAR file, however if you want to build your own custom PHAR you need the follow this steps:


* [Download Caveman](https://github.com/Mamuph/caveman/releases) (The Mamuph Helper Tool)
* Inside the Laralog directory type:

        caveman build . -x -r



