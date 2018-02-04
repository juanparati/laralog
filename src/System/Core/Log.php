<?php


/**
 * Abstract controller class.
 *
 * @package     Mamuph Log
 * @category    Log
 * @author      Mamuph Team
 * @copyright   (c) 2015-2017 Mamuph Team
 *
 * @method static Log   emergency(string $message, array $values = null, string $instance = 'default')
 * @method static Log   alert(string $message, array $values = null, string $instance = 'default')
 * @method static Log   critical(string $message, array $values = null, string $instance = 'default')
 * @method static Log   error(string $message, array $values = null, string $instance = 'default')
 * @method static Log   warning(string $message, array $values = null, string $instance = 'default')
 * @method static Log   notice(string $message, array $values = null, string $instance = 'default')
 * @method static Log   info(string $message, array $values = null, string $instance = 'default')
 * @method static Log   debug(string $message, array $values = null, string $instance = 'default')
 *
 */
abstract class Core_Log implements Core_Contract_Log
{

    // Log message levels - Windows users see PHP Bug #18090
    const EMERGENCY = LOG_EMERG;    // 0
    const ALERT     = LOG_ALERT;    // 1
    const CRITICAL  = LOG_CRIT;     // 2
    const ERROR     = LOG_ERR;      // 3
    const WARNING   = LOG_WARNING;  // 4
    const NOTICE    = LOG_NOTICE;   // 5
    const INFO      = LOG_INFO;     // 6
    const DEBUG     = LOG_DEBUG;    // 7

    /**
     * @var  Log  Singleton instance container
     */
    protected static $_instance = [];

    /**
     * @var  array  list of added messages
     */
    protected $_messages = [];

    /**
     * @var  array  list of log writers
     */
    protected $_writers = [];


    /**
     * Get the singleton instance of this class and enable writing at shutdown.
     *
     * @example
     *
     *     $log = Log::instance();
     *
     * @param   string $name Instance name
     * @return  Core_Contract_Log
     */
    public static function instance(string $name = 'default') : Core_Contract_Log
    {

        if (empty(Log::$_instance[$name])) {
            // Create a new instance
            Log::$_instance[$name] = new Log;

            // Write the logs at shutdown
            register_shutdown_function(array(Log::$_instance[$name], 'write'));
        }

        return Log::$_instance[$name];
    }


    /**
     * Call static helpers
     *
     * @param string $name
     * @param array $arguments
     */
    public static function __callStatic(string $name, array $arguments) : void
    {

        // At least one argument is required
        if (empty($arguments)) return;

        $name = strtoupper($name);

        $that = new ReflectionClass(static::class);
        $levels = array_keys($that->getConstants());

        if (in_array(strtoupper($name), $levels))
        {
            static::instance(empty($arguments[2]) ? 'default' : $arguments[2])->add(
              constant(static::class . '::' . $name),
              $arguments[0],
              empty($arguments[1]) ? [] : $arguments
            );
        }
    }


    /**
     * Attaches a log writer, and optionally limits the levels of messages that
     * will be written by the writer.
     *
     * @example
     *
     *     $log->attach($writer);
     *
     * @param   Log_Writer $writer instance
     * @param   mixed $levels array of messages levels to write OR max level to write
     * @param   int $min_level min level to write IF $levels is not an array
     * @return  Core_Contract_Log
     */
    public function attach(Log_Writer $writer, $levels = [], int $min_level = 0) : Core_Contract_Log
    {

        if (!is_array($levels)) {
            $levels = range($min_level, $levels);
        }

        $this->_writers["{$writer}"] =
          [
            'object' => $writer,
            'levels' => $levels
          ];

        return $this;
    }


    /**
     * Detaches a log writer. The same writer object must be used.
     *
     * @example
     *
     *     $log->detach($writer);
     *
     * @param   Log_Writer $writer instance
     * @return  Core_Contract_Log
     */
    public function detach(Log_Writer $writer) : Core_Contract_Log
    {

        // Remove the writer
        unset($this->_writers["{$writer}"]);

        return $this;
    }


    /**
     * Adds a message to the log. Replacement values must be passed in to be
     * replaced using [strtr](http://php.net/strtr).
     *
     * @example
     *
     *     $log->add(Log::ERROR, 'Could not locate user: :user', array(
     *         ':user' => $username,
     *     ));
     *
     * @param   string $level level of message
     * @param   string $message message body
     * @param   array $values values to replace in the message
     * @param   array $additional additional custom parameters to supply to the log writer
     * @return  Core_Contract_Log
     */
    public function add(
      string $level,
      string $message,
      array $values = null,
      array $additional = null
    ) : Core_Contract_Log
    {

        if ($values) {
            // Insert the values into the message
            $message = strtr($message, $values);
        }

        // Grab a copy of the trace
        if (isset($additional['exception'])) {
            $trace = $additional['exception']->getTrace();
        } else {
            $trace = array_slice(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), 1);
        }

        if ($additional == null) {
            $additional = [];
        }

        // Create a new message
        $this->_messages[] =
        [
          'time'       => time(),
          'level'      => $level,
          'body'       => $message,
          'trace'      => $trace,
          'file'       => isset($trace[0]['file'])     ? $trace[0]['file'] : null,
          'line'       => isset($trace[0]['line'])     ? $trace[0]['line'] : null,
          'class'      => isset($trace[0]['class'])    ? $trace[0]['class'] : null,
          'function'   => isset($trace[0]['function']) ? $trace[0]['function'] : null,
          'additional' => $additional,
        ];

        // Write logs as they are added
        $this->write();

        return $this;
    }


    /**
     * Write and clear all of the messages.
     *
     * @example
     *
     *     $log->write();
     *
     * @return  void
     */
    public function write() : void
    {

        if (empty($this->_messages)) {
            // There is nothing to write, move along
            return;
        }

        // Import all messages locally
        $messages = $this->_messages;

        // Reset the messages array
        $this->_messages = [];

        foreach ($this->_writers as $writer) {

            if (empty($writer['levels'])) {
                // Write all of the messages
                $writer['object']->write($messages);
            } else {
                // Filtered messages
                $filtered = [];

                foreach ($messages as $message) {
                    if (in_array($message['level'], $writer['levels'])) {
                        // Writer accepts this kind of message
                        $filtered[] = $message;
                    }
                }

                // Write the filtered messages
                $writer['object']->write($filtered);
            }
        }
    }

}