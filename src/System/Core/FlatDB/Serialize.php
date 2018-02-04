<?php

/**
 * Serialization driver for FlatDB (Serialization format).
 *
 * @package     Mamuph FlatDB
 * @category    FlatDB
 * @author      Mamuph Team
 * @copyright   (c) 2015-2017 Mamuph Team
 */
class Core_FlatDB_Serialize extends FlatDB_Driver
{

    /**
     * @var string  Path to datafile
     */
    protected $_datafile_path;


    /**
     * Core_FlatDB_Serialize constructor.
     *
     * @param $datafile_path
     * @throws ErrorException
     */
    public function __construct($datafile_path)
    {
        $this->_datafile_path = $datafile_path;

        // Open file if exists
        if (File::exists($this->_datafile_path))
        {
            $this->data = unserialize(file_get_contents($this->_datafile_path));

            if ($this->data === false)
                throw new ErrorException('Unable to open/read ' . $this->_datafile_path);
        }

    }


    /**
     * Writes the memory content into a serialization file
     *
     * @return int|boolean
     */
    public function flush()
    {
        return file_put_contents($this->_datafile_path, serialize($this->data), LOCK_EX);
    }

}
