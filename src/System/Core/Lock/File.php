<?php


/**
 * File lock writer. Writes out in the lock file
 *
 * @package     Mamuph Lock
 * @category    Lock
 * @author      Mamuph Team
 * @copyright   (c) 2015-2018 Mamuph Team
 */
abstract class Core_Lock_File extends Lock_Writer
{

    /**
     * @var  string  Lock filename
     */
    protected $_filename;


    /**
     * Creates/open a file lock. Checks that path exists and file is writable.
     *
     * @example
     *
     *     $lock = new Lock_File($file);
     *
     * @param   string  $path  Lock file name
     */
    public function __construct($path)
    {
        // Set lock file path
        $this->_filename = $path;
    }


    /**
     * Writes on the lock file.
     *
     * @example
     *
     *     $lock->write($data);
     *
     * @param   mixed   $data
     * @return  bool
     */
    public function write($data)
    {
        // Set the name of the log file
        if ( !file_exists($this->_filename))
        {
            // Create the log file
            touch($this->_filename);
        }

        // Write data in lock file in exclusive mode
        return file_put_contents($this->_filename, $data, LOCK_EX);
    }


    /**
     * Read the contents from the lock file.
     *
     * @return string
     */
    public function read()
    {
        return file_get_contents($this->_filename);
    }


    /**
     * Check that lock file exists.
     *
     * @uses File::exists() in order to check if file exists
     * @return bool
     */
    public function exists()
    {
        return File::exists($this->_filename);
    }


    /**
     * Delete the lock file.
     *
     * @return bool
     */
    public function destroy()
    {
        if ($this->exists())
            return unlink($this->_filename);

        return true;
    }


}