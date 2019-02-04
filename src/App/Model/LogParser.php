<?php


use Carbon\Carbon;


class Model_LogParser
{

    /**
     * Regular expression used in order to parse logs.
     *
     * (Group 1) - Timestamp
     * (Group 2) - Environment
     * (Group 3) - Level (DEBUG, INFO, WARNING, ERROR, ALERT...)
     * (Group 4) - Message
     */
    const LOG_EXPR = '/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (.*?)\.(.*?): (.*)/';


    /**
     * Log date time format
     */
    const DATETIME_FORMAT = 'Y-m-d H:i:s';


	/**
	 * Last log.
	 *
	 * @var array
	 */
    protected static $last_log = [];


    /**
     * Parse log entry.
     *
     * @param string $entry
     * @param DateTimeZone $timezone
     * @return array|false
     */
    public static function parseLogEntry(string $entry, DateTimeZone $timezone = null, string $dateformat = 'ISO8601')
    {

    	if (empty($entry))
    		return false;

        if (preg_match(static::LOG_EXPR, $entry, $matches))
        {
            // Extract JSON data
            if (preg_match('/(\{.*\})/U', $matches[4], $data))
            {
                $matches[4] = str_replace($data[1], '', $matches[4]);
                $data       = $data[1];
            }
            else
                $data = null;

            $timestamp = Carbon::createFromFormat(static::DATETIME_FORMAT, $matches[1], $timezone);

            switch ($dateformat)
			{
				case 'epoch':
					$timestamp = $timestamp->timestamp * 1000;
					break;

				case 'timestamp':
					$timestamp = $timestamp->timestamp;
					break;

				default:
					$timestamp = $timestamp->format($dateformat);
					break;
			}

            $log =
			[
				'timestamp'     => $timestamp,
				'environment'   => $matches[2],
				'level'         => $matches[3],
				'message'       => trim($matches[4]),
				'data'          => empty($data) ? null : trim($data)
			];

            // Avoid repeated events
			// // @see: http://thinkofdev.com/equal-identical-and-array-comparison-in-php/
            return static::$last_log = ($log == static::$last_log) ? false : $log;

        }

        return false;
    }


}
