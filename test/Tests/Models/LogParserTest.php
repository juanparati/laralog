<?php
use PHPUnit\Framework\TestCase;


/**
 * Main test group
 */
class LogParserTest extends TestCase
{

    private static $resources = 'Resources{{DS}}';

    private static $timezone  = 'GMT';

    public static function setUpBeforeClass()
    {
        // Set resource path
        self::$resources = TESTPATH . self::$resources;
        self::$resources = str_replace('{{DS}}', DS, self::$resources);
    }


    /**
     * Test LogParser
     *
     * @group   laralog.models.logparser
     */
    public function test_logparser()
    {

        $fp = fopen(self::$resources . 'laravel.log', 'r');

        // Read first line
        $entry = Model_LogParser::parseLogEntry(fgets($fp), new DateTimeZone(self::$timezone));

        // Test first line
        $this->assertEquals('2016-04-16T17:25:45+00:00', $entry['timestamp']);
        $this->assertEquals('local', $entry['environment']);
        $this->assertEquals('INFO', $entry['level']);
        $this->assertEquals('Hello logstash', $entry['message']);
        $this->assertNull($entry['data']);

        // Read second line
        $entry = Model_LogParser::parseLogEntry(fgets($fp), new DateTimeZone(self::$timezone));

        // Test first line
        $this->assertEquals('2016-04-16T17:26:00+00:00', $entry['timestamp']);
        $this->assertEquals('local', $entry['environment']);
        $this->assertEquals('INFO', $entry['level']);
        $this->assertEquals('Example data', $entry['message']);
        $this->assertEquals('{"foo": "Test", "bar": true}', $entry['data']);

    }

}