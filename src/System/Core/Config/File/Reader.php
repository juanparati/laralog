<?php


/**
 * File-based configuration reader. Multiple configuration directories can be
 * used by attaching multiple instances of this class to [Core_Contracts_Config].
 *
 * @package    Mamuph Config
 * @category   Configuration
 * @author     Mamuph Team
 * @copyright  (c) 2009-2017 Mamuph Team
 */
abstract class Core_Config_File_Reader implements Core_Config_Contract_Reader
{

    /**
     * The directory where config files are located
     * @var string
     */
    protected $_directory = '';


    /**
     * Creates a new file reader using the given directory as a config source.
     *
     * @param string $directory Configuration directory to search
     */
    public function __construct(string $directory = 'Config')
    {
        // Set the configuration directory name
        $this->_directory = trim($directory);
    }

    /**
     * Load and merge all of the configuration files in this group.
     *
     * @example
     *
     *      $config->load($name);
     *
     * @param   string $group configuration group name
     * @return  boolean|array
     * @uses    Apprunner::findFile to find file
     * @uses    Apprunner::includes to include file
     */
    public function load(string $group)
    {
        $config = [];

        if ($files = Apprunner::findFile($this->_directory, $group, null,
          true)) {
            foreach ($files as $file) {
                // Merge each file to the configuration array
                $config = Arr::merge($config, Apprunner::includes($file));
            }
        }

        return $config;
    }

}
