<?php

/**
 * Abstract controller class.
 *
 * @package     Mamuph FlatDB
 * @category    FlatDB
 * @author      Mamuph Team
 * @copyright   (c) 2015-2016 Mamuph Team
 */
abstract class Core_FlatDB implements Core_Contract_FlatDB
{

    /**
     * @var  FlatDB  Singleton instance container
     */
    protected static $_instance = [];

    /**
     * @var  FlatDB_Driver  list of drivers
     */
    protected $_driver = null;
    

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
    public static function instance(string $name = 'default') : Core_Contract_FlatDB
    {
        if (empty(FlatDB::$_instance[$name]))
        {
            // Create a new instance
            FlatDB::$_instance[$name] = new FlatDB;

            // Close the file/connection at shutdown
            register_shutdown_function(array(FlatDB::$_instance[$name], 'flush'));
        }

        return FlatDB::$_instance[$name];
    }


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
    public function attach(FlatDB_Driver $driver) : Core_Contract_FlatDB
    {

        $this->_driver = $driver;

        return $this;
    }


    /**
     * Detaches a driver. The same driver object must be used.
     *
     * @example
     *
     *     $flatdb->detach($driver);
     *
     * @return  Core_Contract_FlatDB
     */
    public function detach() : Core_Contract_FlatDB
    {
        // Remove the writer
        unset($this->_driver);

        return $this;
    }


    /**
     * Read a data node by path
     * 
     * @param string    $path       Path (Optional)
     * @param mixed     $default    Default value (Optional when path is used)
     * @return mixed
     */
    public function read(string $path = null, $default = null)
    {
        return $path === null ? $this->_driver->data : Arr::path($this->_driver->data, $path, $default);
    }


    /**
     * Write into the memory a data node by path
     *
     * @param mixed     $data
     * @return void
     */
    public function write($data) : void
    {
        $this->_driver->data = $data;
    }


    /**
     * Copy the memory writes into an external storage (file, apcu, database, etc)
     *
     * @return bool
     */
    public function flush() : bool
    {
        return empty($this->_driver->data) ? false : $this->_driver->flush();
    }

}

