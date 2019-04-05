            __                 __
           / /  ___ ________ _/ /  ___  ___ _
          / /__/ _ `/ __/ _ `/ /__/ _ \/ _ `/
         /____/\_,_/_/  \_,_/____/\___/\_, /
                                      /___/ 

## 1. What is it?

A Laravel log sender agent.

Laralog is suitable for docker containers and small Laravel or Lumen environments that requires to collect and send logs without install the Logstash and the Java runtime.

Laralog is decoupled from Laravel, so it can read and send the Laravel logs even when Laravel fails.

Laralog can send logs to different services like ElasticSearch and Datadog.


## 2. Features

- PHP based solution.
- Distributed as a single PHAR file.
- Compatible with Supervisor.
- Small footprint.
- Easy to configure.
- File handler safe (Logs files can be rotated, linked or truncated on fly).
- Decoupled solution based on [Mamuph framework](http://www.mamuph.org)
- It supports multiple senders.


## 3. Supported senders 

- ElasticSearch (Default)
- Datadog


## 4. Usage examples

- Read logs sequentially without stop and send logs to a single ES instance using a custom index:

        laralog https://myesinstance.lan:9200 --input=laravel.log --index=myindex
        
- Read logs from a external file and logs to a group of servers defined in a file (One instance per line):

        laralog my_es_servers.txt < laravel.log

- Read logs sequentially without stop and send logs asynchronously using a different timezone:

        laralog https://myinstance.lan:9200 --async --to-timezone=Europe/Madrid
        
- Read logs sequentially without stop and send logs to Datadog converting the Laravel log timestamp that uses a different timezone to UTC:
        
        laralog https://http-intake.logs.datadoghq.eu/v1/input/<DATADOG_API_KEY> --sender=datadog --from-timezone=Europe/Madrid

- Read logs sequentially without stop and output the new entries to STDOUT:

        laralog https://myinstance.lan:9200 --input=laravel.log -v
        
- Read logs sequentially without stop and send logs to Datadog using a different service name and overriding the default hostname:

        laralog https://http-intake.logs.datadoghq.eu/v1/input/<DATADOG_API_KEY> --sender=datadog --index=mylogs --hostname=example.net
        

## 5. Parameters

|Parameter|Description|
|--------|----------|
|--input=[laravel.log]|Log file path to read sequentially without stop. When this parameter is not used Laralog will read from the STDIN.|
|--index=[index_name]|Document index.|
|--ignore=[levels]|Error levels to ignore separated by comma.|
|--batch-size=[size]|Batch size used by queue. By default the batch size is 10.|
|--async|Send logs asynchronous using the future mode (Faster approach).|
|--no-check-cert|Do not check the SSL certificate when https is used.|
|--retries=[number]|Number of attemps when logs a sent without success. By default 2 are the number of retries when this parameter is not used.|
|--read-freq=[ms]|File read frequency in milliseconds. 500ms (0.5 sec) is the default value.|
|--from-timezone=[zone]|Read logs from a different timezone. See [timezone list](http://php.net/manual/en/timezones.php).|
|--to-timezone=[zone]|Convert logs to a different timezone See [timezone list](http://php.net/manual/en/timezones.php) (Only ElasticSearch).|
|--sender=[sender]|Sender to use: elasticsearch (Default) or datadog.|
|--hostname=[hostname]|Override the default hostname.|
|-v|Verbose mode that output the new log entries to STDOUT.| 


## 6. Senders

### 6.1 ElasticSearch

#### 6.1.1 Configuration details

Sender name: "elasticsearch" (Default sender).

#### 6.1.2 Limitations and notes

None


### 6.2 Datadog

#### 6.2.1 Configuration details

Sender name: "datadog" (--sender=datadog)

#### 6.2.2 Limitations and notes

- Timestamps are always converted to UTC timezone, so the option "--to-timezone" option is ignored when this sender is used. It is not a issue since Datadog can display the log timestamps according to the timezone configured in their settings.
- Request are always asynchronous, however the "--async" option will improve the request speed because the sender driver will not wait for the response body.
- The "--no-check-cert" will be ignore. It is because all the Datadog servers should always have a valid certificate when "https" is used ([See Datadog API](https://docs.datadoghq.com/api/?lang=bash#logs)).
- It's not recommended to use a value higher than 50 for the "--batch-size" because Datadog do not support more than 50 entries per request ([See Datadog API](https://docs.datadoghq.com/api/?lang=bash#logs)).


## 7. About the mess with timezones

Normally applications and servers use "UTC" as default timezone, however sometimes due operative requirements our apps and mostly their logs are using timestaps formatted for a different timezone.

Laralog will use the configured PHP or OS timezone in order to convert log timestamps (only when "--to-timezone" is used).

In order to deal with timestamps, Laralog provides the following optional parameters:

--from-timezone=[zone] : This parameter specify the Laravel app timezone in other case the default PHP or OS timezone is used.

--to-timezone=[zone] : This parameter specify the target timezone that timestamps are converted (Datadog sender will used always "UTC").


## 8. About the safe file handler

Laralog support different ways to read logs, for example you can redirect the STDIN or tail a log file, and it's fine to use this way when for example you want to send old logs that has not been collected before, however for log files that are writing continuously the best way is to use the "--input=[laravel.log]" parameter so the safe file handler is used.

Benefits of the safe file handler:
- Log files are re-opened when they are re-created due a log rotation, re-linked or truncated.
- Logs are read and sent without stop and on demand.

The safe file handler do not use LibEvent, inotify or another file event notification mechanism so instead it uses a regular file check (See "--read-freq=[ms]" parameter). This makes the log file check process a bit slow but in this way the LibEvent extension is not required.


## 9. How to build Laralog

Laralog is available as a self-executable PHAR file, however if you want to build your own custom PHAR you need the follow this steps:


* [Download Caveman](https://github.com/Mamuph/caveman/releases) (The Mamuph Helper Tool)
* Inside the Laralog directory type:

        caveman build . -x -r


## 10. Download

If you are lazy and you don't want to build your Laralog version, you are welcome to download the following executable (PHAR file):

[Download last release](https://github.com/juanparati/laralog/releases/latest)


## Back by

- [Matchbanker.no](https://matchbanker.no)
