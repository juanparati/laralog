<?php


/**
 * STDERR log writer. Writes out messages to STDERR.
 *
 * @package     Mamuph Log
 * @category    Log
 * @author      Mamuph Team
 * @copyright   (c) 2015-2016 Mamuph Team
 */
class Core_Log_StdErr extends Log_Writer
{
    /**
     * Writes each of the messages to STDERR.
     *
     *     $writer->write($messages);
     *
     * @param   array   $messages
     * @return  void
     */
    public function write(array $messages) : void
    {
        foreach ($messages as $message)
        {
            // Writes out each message
            fwrite(STDERR, $this->formatMessage($message).PHP_EOL);
        }
    }

}
