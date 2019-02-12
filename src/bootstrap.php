<?php
/**
 * Bootstrap
 */


/**
 * Set the default time zone.
 *
 * Uncomment the following line if you don't want to relay to the system timezone.
 *
 * @link http://www.php.net/manual/timezones
 */
// date_default_timezone_set('Europe/Helsinki');


/**
 * Set the default time limit.
 *
 * @link http://php.net/manual/es/function.set-time-limit.php
 */
set_time_limit(0);


/**
 * Ignore user abort the script
 *
 * @link http://php.net/manual/en/function.ignore-user-abort.php
 */
ignore_user_abort(false);


/**
 * Load default internal App configuration
 */
Config::instance()->attach(new Config_File_Reader(CONFIGPATH));


/**
 * Set accepted command line parameters/arguments
 *
 * Parameters are taken from the Config/Params.php
 *
 * Feel free to comment this the following lines if you App do not use command parameters/arguments
 */
Params::process(
    Config::instance()->load('Params')->asArray()
);


/**
 * Define App version
 *
 * App version is obtained from Config/Version.php
 */
define('VERSION', Version::get(
    Config::instance()->load('Version')->asArray()
));


/**
 *  Set the default environment
 *
 *  Possible values are:
 *  Apprunner::DEVELOPMENT
 *  Apprunner::TESTING
 *  Apprunner::PRODUCTION
 */
if (IS_PHAR)
    Apprunner::$environment = Apprunner::PRODUCTION;
else
    Apprunner::$environment = Apprunner::DEVELOPMENT;



/**
 * Instance and set attach log writer
 *
 * Some available writers are:
 * - Log_File
 * - Log_StdErr
 * - Log_StdOut
 * - Log_Syslog
 *
 * For Log_File writer is required to set the log directory.
 * Example:
 * Log::instance('instance_name')->attach(new Log_File('/foo/bar/logs'));
 *
 */
//Log::instance()->attach(new Log_Syslog());
Log::instance()->attach(new Log_StdOut());


/**
 * Initialize Apprunner core
 *
 * Optional settings are:
 * - charset (By default 'utf-8')
 *
 * Example:
 * Apprunner::init(['charset'] => 'iso-8859-15');
 */
Apprunner::init();


/**
 * Load Mamuph modules automatically from Config/Modules.php
 */
Apprunner::modules(
    Config::instance()->load('Modules')->asArray()
);


/**
 * Load PSR-4 Components (Like for example those installed using composer)
 */
if (File::exists(VENDORPATH . 'autoload.php', File::SCOPE_LOCAL)) {
    Apprunner::includes(VENDORPATH . 'autoload.php');
}


/**
 * Instance and set attach config reader/writer
 * It is useful if you want to load external configuration resources.
 *
 * Some available writers are:
 * - Config_File
 *
 * Example:
 * Config::instance('instance_name')->attach(new Config_File('/foo/bar/configs'));
 *
 */
// Config::instance('my_external_conf')->attach(new Config_File('/foo/bar/configs'));


/**
 * Send initialized notification
 */
//Hook::instance()->notify('MAMUPH_INITIALIZED');


/**
 * Call the default controller or entry point
 */
Apprunner::execute('Main');

