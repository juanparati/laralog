<?php


/**
 * File-based configuration writer. Multiple configuration directories can be
 * used by attaching multiple instances of this class to [Core_Config].
 *
 * @package    Mamuph Config
 * @category   Configuration
 * @author     Mamuph Team
 * @copyright  (c) 2009-2017 Mamuph Team
 * @license    http://mamuph.org/license
 */
class Core_Config_File_Writer implements Core_Config_Contract_Writer
{


    /**
     * The directory where config files are located.
     *
     * @var string
     */
    protected $_directory = '';


    /**
     * Creates a new file reader using the given directory as a config source.
     *
     * @param string $directory Configuration directory to search
     */
    public function __construct($directory = 'Config')
    {
        // Set the configuration directory name
        $this->_directory = trim($directory);
    }


    /**
     * Write and merge the configuration.
     *
     * @example
     *
     *      $config->write($group, $key, $config);
     *
     * @param   string $group
     * @param   string $key
     * @param   mixed $value
     * @uses    Apprunner::findFile to find file
     * @uses    Apprunner::includes to include file
     * @return  void
     */
    public function write(string $group, string $key, $value) : void
    {

        if ($files = Apprunner::findFile($this->_directory, $group, null, true)) {
            foreach ($files as $file) {
                // Merge each file to the configuration array
                $sconfig = Apprunner::includes($file);
                $sconfig[$key] = $value;

                // Write buffer content
                file_put_contents($file,
                  "<?php\n return " . var_export($sconfig, true) . ';',
                  LOCK_EX);
            }
        }
    }

}
