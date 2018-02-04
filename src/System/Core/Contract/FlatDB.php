<?php


interface Core_Contract_FlatDB
{

    /**
     * Get the singleton instance of this class and enable writing at shutdown.
     *
     * @example
     *
     *     $flatdb = FlatDB::instance();
     *
     * @param   string  $name   Instance name
     * @return  Core_Contract_FlatDB
     */
    public static function instance(string $name = 'default') : Core_Contract_FlatDB;


    /**
     * Attaches a driver
     *
     * @example
     *
     *     $flatdb->attach($driver);
     *
     * @param   FlatDB_Driver   $driver     instance
     * @return  Core_Contract_FlatDB
     */
    public function attach(FlatDB_Driver $driver) : Core_Contract_FlatDB;


    /**
     * Detaches a driver. The same driver object must be used.
     *
     * @example
     *
     *     $flatdb->detach($driver);
     *
     * @return  Core_Contract_FlatDB
     */
    public function detach() : Core_Contract_FlatDB;


    /**
     * Read a data node by path
     *
     * @param string    $path       Path (Optional)
     * @param mixed     $default    Default value (Optional when path is used)
     * @return mixed
     */
    public function read(string $path = null, $default = null);


    /**
     * Write into the memory a data node by path
     *
     * @param mixed     $data
     * @return void
     */
    public function write($data) : void;


    /**
     * Copy the memory writes into an external storage (file, apcu, database, etc)
     *
     * @return bool
     */
    public function flush() : bool;

}