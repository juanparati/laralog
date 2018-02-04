<?php

interface Core_Contract_Log
{

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
    public static function instance(string $name = 'default') : Core_Contract_Log;


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
     * @param   integer $min_level min level to write IF $levels is not an array
     * @return  Core_Contract_Log
     */
    public function attach(Log_Writer $writer, $levels = [], int $min_level = 0) : Core_Contract_Log;


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
    public function detach(Log_Writer $writer) : Core_Contract_Log;


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
    ) : Core_Contract_Log;


    /**
     * Write and clear all of the messages.
     *
     * @example
     *
     *     $log->write();
     *
     * @return  void
     */
    public function write() : void;

}