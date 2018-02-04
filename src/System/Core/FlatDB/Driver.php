<?php

/**
 * FlatDB driver abstract class.
 *
 * @package     Mamuph FlatDB
 * @category    FlatDB
 * @author      Mamuph Team
 * @copyright   (c) 2015-2016 Mamuph Team
 */
abstract class Core_FlatDB_Driver
{

    /**
     * @var array   In-memory storage
     */
    public $data = [];
    

    /**
     * Storage the data that was wrote in memory
     * 
     * @return bool
     */
    abstract public function flush();

}