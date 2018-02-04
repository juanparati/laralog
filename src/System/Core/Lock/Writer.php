<?php


/**
 * Lock writer abstract class. All [Lock] writers must extend this class.
 *
 * @package     Mamuph Lock
 * @category    Lock
 * @author      Mamuph Team
 * @copyright   (c) 2015-2016 Mamuph Team
 */
abstract class Core_Lock_Writer
{

    /**
     * Write an array of messages.
     *
     * @example
     *
     *     $lock->write($messages);
     *
     * @param   mixed   $messages
     * @return  void
     */
    abstract public function write($messages);


    /**
     * Read the lock file content
     *
     * @example
     *
     *      $lock->read();
     *
     * @return mixed
     */
    abstract public function read();


    /**
     * Remove the lock reference
     *
     * @example
     *
     *      $writer->destroy();
     *
     * @return bool
     */
    abstract public function destroy();


    /**
     * Check that if lock file exists
     *
     * @return mixed
     */
    abstract public function exists();


    /**
     * Allows the writer to have a unique key when stored.
     *
     * @example
     *
     *     echo $lock;
     *
     * @return  string
     */
    final public function __toString()
    {
        return spl_object_hash($this);
    }

}