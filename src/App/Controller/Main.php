<?php

use Revolt\EventLoop;


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

        // Validate arguments
        // ------------------
        $argsval = Params::validate();

        // No arguments, then see the help message
        if (isset($argsval['host']))
        {
            $this->actionHelp();
            Apprunner::terminate();
        }


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


        // Instantiate sender
        // -----------------
		$sender = 'Model_Senders_' . $sender;

		/**
		 * @var $sender Model_Contracts_Sender
		 */
        $this->sender = new $sender($client, Params::get('async'));


        // Set current timezone
		// --------------------
		$current_timezone = Params::get('current_timezone');

		if ($current_timezone && $current_timezone !== true)
			date_default_timezone_set($current_timezone);
		else
			$current_timezone = date_default_timezone_get();

		$current_timezone = new DateTimeZone($current_timezone);


		// Set target timezone
		// -------------------
        $to_timezone = Params::get('timezone');
        $to_timezone = $this->sender->forceUTCTimezone() ? 'UTC' : $to_timezone;
        $to_timezone = $to_timezone ? new DateTimeZone($to_timezone) : null;


		// Set some extra log info
		// -----------------------
        $this->log_info =
		[
			'ignore_levels'    => Params::get('ignore') ?? explode(',', Params::get('ignore')),
			'verbose' 		   => Params::get('verbose'),
			'index' 		   => Params::get('index'),
			'hostname' 		   => Params::get('hostname'),
			'current_timezone' => $current_timezone,
			'to_timezone'      => $to_timezone,
			'dateformat' 	   => $this->sender->getDatetimeFormat(),
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
        EventLoop::repeat(Params::get('read_freq') / 1000, function ()
		{
			$this->readLog();
		});

        EventLoop::onSignal(SIGINT, fn() => $this->actionTerminateBySignal());
        EventLoop::onSignal(SIGHUP, fn() => $this->actionTerminateBySignal());

        EventLoop::run();

		// Finish app
        Apprunner::terminate();
    }


	/**
	 * Read log and send.
	 */
    protected function readLog()
	{

	    $smart = Params::get('smart');

		foreach ($this->reader->getLines() as $entry)
		{

			$entry = Model_LogParser::parseLogEntry(
				$entry,
				$this->log_info['current_timezone'],
				$this->log_info['to_timezone'],
				$this->log_info['dateformat'],
                $smart
			);

			// Parse entry
			if (!$entry)
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
	 * Terminate signal.
	 *
	 * @param int $signal
	 */
	public function actionTerminateBySignal()
	{
		if (!ignore_user_abort())
		{
			echo "Exiting...\n";
            Apprunner::terminate(Apprunner::EXIT_HUP);
		}
	}


}
