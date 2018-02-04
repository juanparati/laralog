<?php


/**
 * Log writer abstract class. All [Log] writers must extend this class.
 *
 * @package     Mamuph Log
 * @category    Log
 * @author      Mamuph Team
 * @copyright   (c) 2015-2017 Mamuph Team
 */
abstract class Core_Log_Writer
{
    /**
     * @var  string  timestamp format for log entries.
     *
     * Defaults timestamp format
     */
    public static $timestamp_format;


    /**
     * Numeric log level to string lookup table.
     * @var array
     */
    protected $_log_levels = [
        LOG_EMERG   => 'EMERGENCY',
        LOG_ALERT   => 'ALERT',
        LOG_CRIT    => 'CRITICAL',
        LOG_ERR     => 'ERROR',
        LOG_WARNING => 'WARNING',
        LOG_NOTICE  => 'NOTICE',
        LOG_INFO    => 'INFO',
        LOG_DEBUG   => 'DEBUG',
    ];


    /**
     * @var  int  Level to use for stack traces
     */
    public static $strace_level = LOG_DEBUG;


    /**
     * Write an array of messages.
     *
     *     $writer->write($messages);
     *
     * @param   array   $messages
     * @return  void
     */
    abstract public function write(array $messages) : void;


    /**
     * Allows the writer to have a unique key when stored.
     *
     *     echo $writer;
     *
     * @return  string
     */
    final public function __toString()
    {
        return spl_object_hash($this);
    }


    /**
     * Formats a log entry.
     *
     * @param   array   $message
     * @param   string  $format
     * @return  string
     */
    public function formatMessage(array $message, $format = "time --- level: body in file:line") : string
    {
        $time = new DateTime('@' . $message['time']);

        $message['time'] = $time->format(Log_Writer::$timestamp_format);
        $message['level'] = $this->_log_levels[$message['level']];

        $string = strtr($format, array_filter($message, 'is_scalar'));

        if (isset($message['additional']['exception']))
        {
            // Re-use as much as possible, just resetting the body to the trace
            $message['body'] = $message['additional']['exception']->getTraceAsString();
            $message['level'] = $this->_log_levels[Log_Writer::$strace_level];

            $string .= PHP_EOL . strtr($format, array_filter($message, 'is_scalar'));
        }

        return $string;
    }


}