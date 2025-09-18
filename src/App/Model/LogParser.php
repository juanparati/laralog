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
     * @param DateTimeZone|null $current_timezone
     * @param DateTimeZone|null $to_timezone
     * @param string $dateformat
     * @param bool $smart_serialization
     * @return array|false
     */
    public static function parseLogEntry(
        string       $entry,
        DateTimeZone $current_timezone = null,
        DateTimeZone $to_timezone = null,
        string       $dateformat = 'DATE_ISO8601',
        bool         $smart_serialization = false
    )
    {

        if (empty($entry))
            return false;

        if (preg_match(static::LOG_EXPR, $entry, $matches)) {

            $structs = [];
            $initialMatchLength = strlen($matches[4]);

            // Extract JSON data
            foreach (static::jsonExtract($matches[4]) as $struct) {
                $structs[] = $struct['string'];
                $reduction = $initialMatchLength - strlen($matches[4]);
                $matches[4] = substr_replace($matches[4], '', $struct['start'] - $reduction, $struct['length']);
            }

            $timestamp = Carbon::createFromFormat(static::DATETIME_FORMAT, $matches[1], $current_timezone);

            if ($to_timezone)
                $timestamp->setTimezone($to_timezone);

            switch ($dateformat) {
                case 'epoch':
                    $timestamp = time() * 1000;
                    break;

                case 'timestamp':
                    $timestamp = $timestamp->getTimestamp();
                    break;

                default:
                    if (strpos($dateformat, 'DATE_') === 0)
                        $dateformat = constant($dateformat);

                    $timestamp = $timestamp->format($dateformat);
                    break;
            }


            $log =
                [
                    'timestamp'   => $timestamp,
                    'environment' => $matches[2],
                    'level'       => $matches[3],
                    'message'     => trim($matches[4]),
                    'data'        => $structs ? null : json_encode($structs),
                ];


            $fnSafeDecode = function ($str) {
                $des = json_decode($str, true);
                return JSON_ERROR_NONE === json_last_error() ? $des : null;
            };


            /**
             * Deserialize data and add it to params.
             */
            if (!empty($structs) && $smart_serialization) {
                if (count($structs) === 1) {
                    $log['params'] = $fnSafeDecode($structs[0]);
                } else {
                    $param = [];
                    foreach ($structs as $struct) {
                        $param[] = $fnSafeDecode($struct);
                    }

                    $log['params'] = array_filter($param);
                }
            }

            // Avoid repeated events
            return static::$last_log = ($log == static::$last_log) ? false : $log;

        }

        return false;
    }


    /**
     * Extract JSON strings from a string.
     *
     * This function is faster than use regular expressions.
     *
     * @param string $str
     * @return array<array{start:int,length:int,string:string}>
     */
    protected static function jsonExtract(string $str): array
    {
        $numBrackets = $initStr = $foundAt = -1;
        $jsonStrings = [];

        foreach (str_split($str) as $k => $chr) {
            switch ($chr) {
                case '{':
                    $numBrackets++;

                    if ($initStr === -1) {
                        $initStr = $k;
                    }
                    break;

                case '}':
                    if ($initStr >= 0) {
                        $numBrackets--;

                        if ($numBrackets === -1 && $foundAt > -1) {
                            $jsonStrings[] = [
                                'start'  => $foundAt,
                                'length' => $k - $foundAt + 1,
                                'string' => substr($str, $foundAt, $k - $foundAt + 1),
                            ];

                            $numBrackets   = $initStr = $foundAt = -1;
                        }
                    }
                    break;

                case '"':
                    if ($numBrackets > -1 && $foundAt === -1) {
                        $foundAt = $initStr;
                    }
                    break;
            }
        }

        return $jsonStrings;
    }


}
