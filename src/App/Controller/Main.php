<?php

use Amp\Loop;


/**
 * Default controller entry-point
 */
class Controller_Main extends Controller
{

	/**
	 * Maximum line height.
	 */
	const MAX_LINE_HEIGHT = 32768;


    /**
     * Climate instance.
     *
     * @var \League\CLImate\CLImate
     */
    protected $console;


	/**
	 * Sender instance.
	 *
	 * @var Model_Contracts_Sender.
	 */
    protected $sender;


	/**
	 * Reader.
	 *
	 * @var Model_Contracts_Reader
	 */
    protected $reader;


	/**
	 * Log info.
	 *
	 * @var array
	 */
	protected $log_info = [];


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

		// Attach your IPC signals event handling
		// --------------------------------------
		Hook::instance()->attach('IPC_SIGHUP', [$this, 'actionTerminateBySignal']);
		Hook::instance()->attach('IPC_SIGINT', [$this, 'actionTerminateBySignal']);


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


        // Setup client
        // ------------
		$sender = str_replace('_', '', ucwords(Params::get('sender'), '_'));
		$client = 'Model_Clients_' . $sender;

		if (!class_exists($client))
		{
			$this->console->error('Sender not available');
			Apprunner::terminate(Apprunner::EXIT_FAILURE);
		}

		/**
		 * @var $client Model_Contracts_Client
		 */
		$client = new $client(
			$hosts,
			(int) Params::get('retries'),
			!Params::get('no-check-cert'),
			(int) Params::get('batch_size')
		);


        // Instatiate sender
        // -----------------
		$sender = 'Model_Senders_' . $sender;

		/**
		 * @var $sender Model_Contracts_Sender
		 */
        $this->sender = new $sender($client, Params::get('async'));


		// Set some extra log info
		// -----------------------
        $this->log_info =
		[
			'ignore_levels' => Params::get('ignore') ?? explode(',', Params::get('ignore')),
			'verbose' 		=> Params::get('verbose'),
			'index' 		=> Params::get('index'),
			'hostname' 		=> Params::get('hostname'),
			'timezone' 		=> $timezone,
			'dateformat' 	=> $this->sender->getDatetimeFormat(),
		];


        // Instantiate reader
		// ------------------
        $input = Params::get('input');

		if ($input === 'php://stdin')
			$this->reader = new Model_Readers_Stdin();
		else
			$this->reader = new Model_Readers_Stream(File::parsePath($input));


        // Initialize event loop
		// ---------------------
		Loop::run(function ()
		{
			// "repeat" is used instead of "onReadable", because unfortunately not all
			// PHP installations include the libevent extension.
			Loop::repeat(Params::get('read_freq'), Closure::fromCallable([$this, 'readLog']));
		});

        // Your app finish here
        Apprunner::terminate(Apprunner::EXIT_SUCCESS);

    }


    protected function readLog()
	{

		foreach ($this->reader->getLines() as $entry)
		{

			// Parse entry
			if (!$entry = Model_LogParser::parseLogEntry($entry, $this->log_info['timezone'], $this->log_info['dateformat']))
				continue;

			// Ignore levels
			if (!empty($this->log_info['ignore_levels']) && in_array($entry['level'], $this->log_info['ignore_levels']))
				continue;

			// Add hostname
			$entry['hostname'] = $this->log_info['hostname'];

			// Display entries when verbose mode is used
			if ($this->log_info['verbose'])
				$this->console->json($entry);

			// Send to sender
			try
			{
				$this->sender->send($this->log_info['index'], $entry);
			}
			catch (Exception $e)
			{
				$this->console->error('Unable to send logs ' . $e->getMessage());
			}

		}


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
	 * Action received when
	 * @param int $signal
	 */
	public function actionTerminateBySignal(int $signal)
	{
		if (!ignore_user_abort())
		{
			// Close reader
			@fclose($this->reader);

			$exit_status = Apprunner::EXIT_FAILURE;

			switch ($signal)
			{
				// SIGHUP
				case 1:
					$exit_status = Apprunner::EXIT_HUP;
					break;
				// SIGINT
				case 2:
					$exit_status = Apprunner::EXIT_CTRLC;
			}

			echo "Exiting...";

			Apprunner::terminate($exit_status);
		}
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
