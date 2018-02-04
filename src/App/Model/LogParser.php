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
     * Parse log entry.
     *
     * @param string $entry
     * @param DateTimeZone $timezone
     * @return array|bool
     */
    public static function parseLogEntry(string $entry, DateTimeZone $timezone = null)
    {

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


            return
            [
                'timestamp'     => Carbon::createFromFormat(static::DATETIME_FORMAT, $matches[1], $timezone)->toIso8601String(),
                'environment'   => $matches[2],
                'level'         => $matches[3],
                'message'       => trim($matches[4]),
                'data'          => empty($data) ? null : trim($data)
            ];
        }

        return false;
    }


}