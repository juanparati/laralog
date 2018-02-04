<?php
/**
 * Mamuph entry point
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
if (isset($custom_reporting))
{
    error_reporting($custom_reporting);
    unset($custom_reporting);
}
else
    error_reporting(E_ALL & ~E_DEPRECATED);


/**
 * Required in order to process command line arguments
 *
 * @link http://php.net/manual/es/configuration.changes.php
 */
ini_set('register_argc_argv', true);


/**
 * Autodetect if current file is a phar file
 */
$phar_running = Phar::running();
define('IS_PHAR', !empty($phar_running));

/**
 * Set standard directory separator
 */
define('DS', IS_PHAR ? '/' : DIRECTORY_SEPARATOR);

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
 * The directory in which resources are located (images, text files, binaries...)
 */
$resources = 'Resources';

/**
 * The directory in which the third party libraries are located
 */
$vendor = 'Vendor';

/**
 * App configuration files
 */
$config = 'Config';


/**
 * Define an absolute App ID
 */
if (!defined('APPID'))
    define('APPID', uniqid('mamuph_', true));


/**
 * Define absolute paths for configured directories
 */
define('BASEPATH',      IS_PHAR ? 'phar://' . APPID . '.phar/' : realpath(dirname(__FILE__)) . DS);
define('SYSPATH',       BASEPATH . $system    . DS);
define('APPPATH',       BASEPATH . $app       . DS);
define('MODPATH',       BASEPATH . $modules   . DS);
define('RESOURCESPATH', BASEPATH . $resources . DS);
define('VENDORPATH',    BASEPATH . $vendor    . DS);
define('CONFIGPATH',    BASEPATH . $config    . DS);


/**
 * Clean up initialization vars
 */
unset($phar_running, $system, $app, $modules, $resources, $vendor, $config);


/**
 * Load the base class Apprunner
 */
require SYSPATH . 'Core' . DS . 'Apprunner.php';

if (is_file(APPPATH . 'Apprunner.php'))
{
    // Application extends the core
    require APPPATH . 'Apprunner.php';
}
else
{
    // Load the base extension
    require SYSPATH . 'Apprunner.php';
}


/**
 * Enable autoloader
 */
spl_autoload_register(array('Apprunner', 'autoLoad'));


/**
 * Initialize hooks
 */
Hook::instance();


/**
 * Set current path
 */
chdir(getcwd());


/**
 * Launch bootstrap
 */
Apprunner::requires(BASEPATH . 'bootstrap.php');







