<?php


/**
 * Abstract model class.
 *
 * @package     Mamuph Lock
 * @category    Lock
 * @author      Mamuph Team
 * @copyright   (c) 2015-2017 Mamuph Team
 */
abstract class Core_Lock implements Core_Contract_Lock
{

    /**
     * @var  Log  Singleton instance container.
     */
    protected static $_instance = [];


    /**
     * @var  Lock_Writer  Writer instance.
     */
    protected $_writer;


    /**
     * Default dead message.
     *
     * 'false' is used when dead message is not used and lock file is not removed
     * 'null' is used when lock file should be removed
     * otherwise dead message is written
     *
     * @var mixed
     */
    public $dead_message = null;


    /**
     * Get the singleton instance of this class and enable writing at shutdown.
     *
     * @example
     *
     *     $lock = Lock::instance($locker_id);
     *
     * @param   string  Lock instance ID
     * @return  Core_Contract_Lock
     */
    public static function instance(string $id = 'default') : Core_Contract_Lock
    {
        if (!isset(Lock::$_instance[$id]) || Lock::$_instance[$id] === null)
        {
            // Create a new instance
            Lock::$_instance[$id] = new Lock();

            // Write lock on shutdown
            register_shutdown_function(array(Lock::$_instance[$id], 'dead_message'));
        }

        return Lock::$_instance[$id];
    }


    /**
     * Attaches a lock writer.
     *
     * @example
     *
     *     $lock->attach($writer);
     *
     * @param   Lock_Writer  $writer    instance
     * @return  Core_Contract_Lock
     */
    public function attach(Lock_Writer $writer) : Core_Contract_Lock
    {
        $this->_writer = $writer;

        return $this;
    }


    /**
     * Detaches a lock writer. The same writer object must be used.
     *
     * @example
     *
     *     $lock->detach($writer);
     *
     * @return  Core_Contract_Lock
     */
    public function detach() : Core_Contract_Lock
    {
        // Remove the writer
        unset($this->_writer);

        return $this;
    }


    /**
     * Write automatically a dead message.
     *
     * @return  void
     */
    public function deadMessage() : void
    {
        if ($this->dead_message === null)
            $this->destroy();
        else
            $this->write($this->dead_message);
    }


    /**
     * Write in lockfile.
     *
     * @example
     *
     *     $lock->write($data);
     *
     * @param   mixed   $data   Data or string
     * @return  void
     */
    public function write($data) : void
    {
        // Write the filtered messages
        $this->_writer->write($data);
    }


    /**
     * Read a lockfile.
     *
     * @example
     *
     *      $lock->read();
     *
     * @return  mixed
     */
    public function read()
    {
        return $this->_writer->read();
    }


    /**
     * Check that Lock exists.
     *
     * @return bool
     */
    public function exists() : bool
    {
        return $this->_writer->exists();
    }


    /**
     * Delete the lockfile.
     *
     * @return bool
     */
    public function destroy() : bool
    {
        $this->dead_message = null;

        return $this->_writer->destroy();
    }

}