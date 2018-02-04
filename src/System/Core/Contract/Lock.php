<?php


interface Core_Contract_Lock
{

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
    public static function instance(string $id = 'default') : Core_Contract_Lock;


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
    public function attach(Lock_Writer $writer) : Core_Contract_Lock;


    /**
     * Detaches a lock writer. The same writer object must be used.
     *
     * @example
     *
     *     $lock->detach($writer);
     *
     * @return  Core_Contract_Lock
     */
    public function detach() : Core_Contract_Lock;


    /**
     * Write automatically a dead message.
     *
     * @return  void
     */
    public function deadMessage() : void;


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
    public function write($data) : void;


    /**
     * Read a lockfile.
     *
     * @example
     *
     *      $lock->read();
     *
     * @return  mixed
     */
    public function read();


    /**
     * Check that Lock exists.
     *
     * @return bool
     */
    public function exists() : bool;


    /**
     * Delete the lockfile.
     *
     * @return bool
     */
    public function destroy() : bool;

}