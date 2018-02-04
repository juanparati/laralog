<?php

use Elasticsearch\ClientBuilder;


/**
 * Default controller entry-point
 */
class Controller_Main extends Controller
{

    /**
     * Climate instance
     *
     * @var \League\CLImate\CLImate
     */
    protected $console;


    /**
     * @var \Elasticsearch\Client
     */
    protected $elastic_client;


    /**
     * Controller_Main constructor.
     */
    public function __construct()
    {
        $this->console  = new League\CLImate\CLImate;
    }


    /**
     * Entry point
     */
    public function actionMain()
    {

        // Validate arguments
        // ------------------
        $argsval = Params::validate();

        // No arguments, then see the help message
        if (isset($argsval['host']))
        {
            $this->actionHelp();
            Apprunner::terminate();
        }


        $timezone = Params::get('timezone');

        // Set timezone
        // ------------
        if ($timezone && $timezone !== true)
            date_default_timezone_set($timezone);
        else
            $timezone = date_default_timezone_get();

        $timezone = new DateTimeZone($timezone);


        // Install signal handlers
        // -----------------------
        Hook::instance()->attach('UNIX_SIGINT' , [$this, 'terminate']);
        Hook::instance()->attach('UNIX_SIGTERM', [$this, 'terminate']);


        // Read host from file
        // -------------------
        $hosts = Params::get('host');

        if (File::exists($hosts, File::SCOPE_EXTERNAL))
            $hosts = file($hosts, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
        else
            $hosts = [$hosts];


        // Setup ElasticSearch connection
        // ------------------------------
        $this->elastic_client = ClientBuilder::create()
            ->setHosts($hosts)
            ->setRetries(Params::get('retries'))
            ->setSSLVerification(!Params::get('no-check-cert'))
            ->setHandler(ClientBuilder::defaultHandler(['max_handles' => Params::get('batch_size')]))
            ->build();


        // Instatiate sender
        // -----------------
        $sender = new Model_LogSender($this->elastic_client);

        $ignore_levels = Params::get('ignore') ?? explode(',', Params::get('ignore'));
        $verbose       = Params::get('verbose');
        $index         = Params::get('index');
        $hostname      = gethostname();
        $input         = Params::get('input');

        $reader = new Model_StreamReader($input);


        // Read, parse and send
        // --------------------
        while (1)
        {

            usleep(Params::get('read_freq') * 1000);

            foreach ($reader->getLines() as $entry)
            {

                // Parse entry
                if (!$entry = Model_LogParser::parseLogEntry($entry, $timezone))
                    continue;

                // Ignore levels
                if (!empty($ignore_levels) && in_array($entry['level'], $ignore_levels))
                    continue;

                // Add hostname
                $entry['hostname'] = $hostname;

                // Display entries when verbose mode is used
                if ($verbose)
                    print_r($entry);

                // Send to ElasticSearch
                try
                {
                    $sender->send($index, $entry);
                }
                catch (Exception $e)
                {
                    $this->console->error('Unable to send logs ' . $e->getMessage());
                }

            }

        }


        // Your app finish here
        Apprunner::terminate(Apprunner::EXIT_SUCCESS);

    }


    /**
     * Display the help
     */
    public function actionHelp()
    {

        $help = file_get_contents(APPPATH . 'View/help.txt');
        $help = str_replace('#{{__EXECUTABLE__}}', basename(Phar::running()), $help);
        $help = str_replace('#{{VERSION}}', VERSION, $help);

        // Output help file
        $this->console->out($help);
    }


    /**
     * Finish program
     */
    public function terminate()
    {
        $this->console->out('Leaving...');
        Apprunner::terminate(Apprunner::EXIT_SUCCESS);
    }

}
