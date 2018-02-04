<?php
/**
 * Mamuph test entrypoint
 */

/**
 * Set the PHP error reporting level. If you set this in php.ini, you remove this.
 * @link http://www.php.net/manual/errorfunc.configuration#ini.error-reporting
 *
 * When developing your application, it is highly recommended to enable notices
 * and strict warnings. Enable them by using: E_ALL | E_STRICT
 *
 * In a production environment, it is safe to ignore notices and strict warnings.
 * Disable them by using: E_ALL ^ E_NOTICE
 *
 * When using a legacy application with PHP >= 5.3, it is recommended to disable
 * deprecated notices. Disable with: E_ALL & ~E_DEPRECATED
 */
error_reporting(E_ALL);


/**
 * Required in order to process command line arguments
 *
 * @link http://php.net/manual/es/configuration.changes.php
 */
ini_set('register_argc_argv', true);


/**
 * Test are not executed in PHAR files
 */
define('IS_PHAR', FALSE);

/**
 * Set standard directory separator
 */
define('DS', DIRECTORY_SEPARATOR);


/**
 * The directory in which the System framework is located
 */
$system = 'System';

/**
 * The directory in which your App source is located
 */
$app = 'App';

/**
 * The directory in which the native extensions and modules are located
 */
$modules = 'Modules';

/**
 * The directory in which the third party libraries are located
 */
$vendor = 'Vendor';

/**
 * App configuration files
 */
$config = 'Config';


// Define absolute paths for configured directories
define('TESTPATH',   realpath(dirname(__FILE__)) . DS);
define('BASEPATH',   TESTPATH . '..' . DS . 'src' . DS);
define('SYSPATH',    BASEPATH . $system . DS);
define('APPPATH',    BASEPATH . $app    . DS);
define('MODPATH',    BASEPATH . $modules. DS);
define('VENDORPATH', BASEPATH . $vendor . DS);
define('CONFIGPATH', BASEPATH . $config . DS);

// Clean up initialization vars
unset($system, $app, $modules, $vendor, $config);

// Load the base class Apprunner
require SYSPATH . 'Core' . DS . 'Apprunner.php';

if (is_file(APPPATH . 'Apprunner.php'))
{
    // Application extends the core
    require APPPATH . 'Apprunner.php';
}
else
{
    // Load the base extension
    require SYSPATH  . 'Apprunner.php';
}

// Enable autoloader
spl_autoload_register(array('Apprunner', 'autoLoad'));

// Load default internal App configuration
Config::instance()->attach(new Config_File_Reader(CONFIGPATH));

/**
 * Set current path
 */
chdir(getcwd());


// Launch bootstrap
Apprunner::requires(TESTPATH . 'bootstrap.php');







