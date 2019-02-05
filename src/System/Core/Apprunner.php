<?php


/**
 * Abstract Apprunner class. Apprunner is the Mamuph core class
 *
 * @package     Mamuph Core
 * @category    Apprunner
 * @author      Mamuph Team
 * @copyright   (c) 2015-2018 Mamuph Team
 */
abstract class Core_Apprunner
{

    // Default namespace prefix
    const NAMESPACE_PREFIX = 'Mamuph\\';


    // Mamuph core version
    const VERSION = '2.2';


    // Environment constants as bitmask
    const DEVELOPMENT = 0b0001;
    const TESTING     = 0b0010;
    const STAGING     = 0b0100;
    const PRODUCTION  = 0b1000;


    // Default exit codes
    // @See: https://linux.die.net/include/sysexits.h
    const EXIT_SUCCESS     = 0;    // Successful termination (Everything is ok)
    const EXIT_FAILURE     = 1;    // Catchall for general errors
    const EXIT_USAGE       = 64;   // Command line usage error
    const EXIT_DATAERR     = 65;   // Data format error
    const EXIT_NOINPUT     = 66;   // Cannot open input
    const EXIT_NOUSER      = 67;   // Address unknown
    const EXIT_NOHOST      = 68;   // Host name unknown
    const EXIT_UNAVAILABLE = 69;   // Service unavailable
    const EXIT_SOFTWARE    = 70;   // Internal software error
    const EXIT_OSERR       = 71;   // System error
    const EXIT_OSFILE      = 72;   // Critical OS file missing
    const EXIT_CANTCREAT   = 73;   // Cant't create output file
    const EXIT_IOERR       = 74;   // Input/Output error
    const EXIT_TEMPFAIL    = 75;   // Temp failure
    const EXIT_PROTOCOL    = 76;   // Remote error in protocol
    const EXIT_NOPERM      = 77;   // Permission denied
    const EXIT_CONFIG      = 78;   // Configuration error
    const EXIT_HUP         = 129;  // Script terminated by Hang up signal
    const EXIT_CTRLC       = 130;  // Script terminated by Control-C


    /**
     * @var string  Default environment
     */
    public static $environment = Apprunner::DEVELOPMENT;


    /**
     * @var string  Character set of input and output
     */
    public static $charset = 'utf-8';


    /**
     * @var array   Include paths that are used to find files
     */
    protected static $_paths = [APPPATH, SYSPATH, CONFIGPATH];


    /**
     * @var array   Loaded modules
     */
    protected static $_modules = [];


    /**
     * Initializes the environment:
     *
     * - Determines the current environment
     * - Set global settings
     *
     * The following settings can be set:
     *
     * Type      | Setting    | Description                                    | Default Value
     * ----------|------------|------------------------------------------------|---------------
     * string    | charset    | Character set used for all input and output    | "utf-8"
     *
     * @throws  Exception
     * @param   array $settings Array of settings.  See above.
     * @return  void
     */
    public static function init(array $settings = null) : void
    {

        /**
         * Enable xdebug parameter collection in development mode to improve fatal stack traces.
         */
        if (Apprunner::$environment == Apprunner::DEVELOPMENT AND extension_loaded('xdebug')) {
            ini_set('xdebug.collect_params', 3);
        }

        if (isset($settings['charset'])) {
            // Set the system character set
            Apprunner::$charset = strtolower($settings['charset']);
        }

        if (function_exists('mb_internal_encoding')) {
            // Set the MB extension encoding to the same character set
            mb_internal_encoding(Apprunner::$charset);
        }

    }


    /**
     * Autoloader class
     *
     * You should never call this function because this is the default
     * autoloader method.
     *
     * @param   string $class Classname
     * @return  void
     */
    final public static function autoLoad(string $class) : void
    {

        // Transform the class name according to PSR-0
        $class = ltrim($class, '\\');
        $file = '';
        $namespace = '';

        if ($last_namespace_position = strrpos($class, '\\')) {
            // Move to the next registered autoloader when namespace is not the default one
            if (strpos($class, Apprunner::NAMESPACE_PREFIX) !== 0) {
                return;
            }

            $namespace = substr($class, 0, $last_namespace_position);
            $class = substr($class, $last_namespace_position + 1);
            $file = str_replace('\\', DS, $namespace) . DS;
        }

        $file .= str_replace('_', DS, $class);

        if ($path = Apprunner::findFile('', $file)) {
            // Load the class file
            self::requires($path);
        }

        // Class is not in the filesystem, so we move to the next registered autoloader...

    }


    /**
     * Find a file.
     *
     * @param   string $dir
     * @param   string $file
     * @param   string $ext
     * @param   bool|FALSE $array
     * @return  bool|string|array
     */
    public static function findFile(
      string $dir,
      string $file,
      string $ext = null,
      bool   $array = false)
    {

        if ($ext === null) {
            // Use the default extension
            $ext = '.php';
        } elseif ($ext) {
            // Prefix the extension with a period
            $ext = ".{$ext}";
        } else {
            // Use no extension
            $ext = '';
        }

        // Add directory separator
        $dir .= !empty($dir) && $dir[mb_strlen($dir) - 1] === DS ? '' : DS;

        // Create a partial path of the filename
        $path = $dir . $file . $ext;

        if (is_file($path)) {
            return $array ? [$path] : $path;
        }

        if ($array || $dir === 'Config') {

            // Include paths must be searched in reverse
            $paths = array_reverse(Apprunner::$_paths);

            // Array of files that have been found
            $found = [];

            foreach ($paths as $dir) {
                if (is_file($dir . $path)) {
                    // This path has a file, add it to the list
                    $found[] = $dir . $path;
                }
            }

        } else {

            // The file has not been found yet
            $found = false;

            foreach (Apprunner::$_paths as $dir) {

                if (is_file($dir . $path)) {
                    // A path has been found
                    $found = $dir . $path;

                    // Stop searching
                    break;
                }
            }
        }

        return $found;
    }


    /**
     * Load script using the require function.
     *
     * @param string $file
     * @return mixed
     */
    public static function requires(string $file)
    {
        return require(static::replaceDirSep($file));
    }


    /**
     * Replace the wrong directory separator by the right one.
     *
     * @param string $file
     * @return mixed
     */
    public static function replaceDirSep(string $file)
    {

        if (IS_PHAR) {
            $file = str_replace('\\', '/', $file);
        } else {
            $file = str_replace(DIRECTORY_SEPARATOR == '/' ? '\\' : '/', DS,
              $file);
        }

        return $file;

    }


    /**
     * Changes the currently enabled modules. Module paths may be relative
     * or absolute, but must point to a directory:
     *
     * @example
     *
     *      Apprunner::modules(array('foomodule' => array('path' => MODPATH.'foo'));
     *
     * @param array|NULL $modules
     * @return array
     * @throws Exception
     */
    public static function modules(array $modules = null) : array
    {
        if ($modules === null) {
            // Not changing modules, just return the current set
            return Apprunner::$_modules;
        }

        // Start a new list of include paths, APPPATH first
        $paths = [];

        foreach ($modules as $name => $module) {

            if (!isset($module['enable_on']) || ($module['enable_on'] & Apprunner::$environment)) {

                if (is_dir($module['path'])) {
                    // Add the module to include paths
                    // @ToDo: Add alternative to realpath when PHAR is executed
                    $paths[] = $modules[$name]['path'] = realpath($module['path']) . DS;
                } else {
                    // This module is invalid, remove it
                    throw new Exception('Attempted to load an invalid or missing module ' . $name);
                }

            }
        }

        // Set the new include paths
        Apprunner::$_paths = Arr::merge(Apprunner::$_paths, $paths);

        // Set the current module list
        Apprunner::$_modules = $modules;

        foreach (Apprunner::$_modules as $module) {
            $init = $module['path'] . 'init.php';

            if (is_file($init)) {
                // Include the module initialization file once
                require_once $init;
            }
        }

        return Apprunner::$_modules;
    }


    /**
     * Load script using the include function.
     *
     * @param string $file
     * @return mixed
     */
    public static function includes(string $file)
    {
        $file = File::parsePath($file);

        return include(self::replaceDirSep($file));
    }


    /**
     * Load and execute an action defined into a Controller.
     * By default the "action_main" is called.
     *
     * @example
     *
     *      Apprunner::execute('MyController');
     *
     * @return mixed
     */
    public static function execute()
    {
        $args = func_get_args();
        $args_num = func_num_args();

        $file = $args[0];

        $controller_name = 'Controller_' . $file;

        $controller_method = empty($args[1]) ? 'actionMain' : 'action' . ucfirst($args[1]);

        $controller = new $controller_name;

        if ($args_num > 1) {

            // Remove just the first argument (Controller name)
            array_shift($args);

            // Remove the second argument (Controller name and Controller method)
            if ($args_num > 2) {
                array_shift($args);
            }

            // Call dynamically to the default_method
            return call_user_func_array(array($controller, $controller_method),
              count($args) === 0 ? null : $args);
        } else {
            return $controller->actionMain();
        }
    }


    /**
     * Non-blocking version of sleep.
     *
     * @param float $seconds
     */
    public static function sleep(float $seconds)
    {
        $stop_time = microtime(true) + $seconds;

        while (microtime(true) < $stop_time) {
            if (function_exists('pcntl_signal_dispatch')) {
                pcntl_signal_dispatch();
            }
        }
    }


    /**
     * Terminate the program execution and return an exit code.
     *
     * @example
     *
     *
     *      Apprunner::terminate(Apprunner::EXIT_SUCESSS);
     *
     *      // OR
     *
     *      Apprunner::terminate(Apprunner::EXIT_FAILURE);
     *
     * @param int $exit_code
     * @return void
     */
    public static function terminate(int $exit_code = self::EXIT_SUCCESS) : void
    {
        // @ToDO: call unload routines
        exit($exit_code);
    }

}
