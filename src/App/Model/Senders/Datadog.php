<?php


use Amp\Artax\Client;
use Amp\Artax\Request;


/**
 * Class Model_Senders_Datadog
 *
 * Send logs to Datadog.
 */
class Model_Senders_Datadog implements Model_Contracts_Sender
{

    /**
     * Datadog client.
     *
     * @var \Amp\Artax\DefaultClient
     */
    protected $client;


	/**
	 * Client configuration.
	 *
	 * @var array
	 */
    protected $client_settings = [];


    /**
     * Last entry.
     *
     * @var array
     */
    protected $last_entry = [];


    /**
     * Use future mode.
     *
     * @var bool $future_mode
     */
    protected $async = false;


	/**
	 * Batch queue.
	 *
	 * @var array
	 */
    protected $batch = [];


    /**
     * Model_LogSender constructor.
     *
     * @param Model_Contracts_Client $client
     * @param bool $async
     */
    public function __construct($client, bool $async = false)
    {
        $this->client = $client->getClient();
		$this->client->setOption(Client::OP_DISCARD_BODY, $async);

		$this->client_settings = $client->getClientSettings();

        $this->async  = $async;
    }


	/**
	 * Send all logs when the object is destruct
	 */
    public function __destruct()
	{
		foreach (array_keys($this->batch) as $index)
			$this->sendNow($index);
	}


	/**
     * Send log entry.
     *
     * @param string $index
     * @param array $body
     * @param string $type
     */
    public function send(string $index, array $body)
    {
		$this->batch[$index][] = $body;

		if (count($this->batch[$index]) >= $this->client_settings['batch_size'])
			$this->sendNow($index);

    }


	/**
	 * Send logs immediately.
	 *
	 * @param string $index
	 * @return Generator
	 */
    protected function sendNow(string $index)
	{
		$query_params = http_build_query([
			'ddsource' => 'log',
			'service'  => $index,
			'hostname' => $this->batch[$index][0]['hostname']
		]);

		$request = (new Request($this->client_settings['hosts'][0] . '?' . $query_params, 'POST'))
			->withHeader('Content-Type', 'application/json')
			->withBody(json_encode($this->batch[$index]));

		$this->client->request($request)->onResolve(function ($error, $response) {
			if ($error && Params::get('verbose'))
			{
				echo 'Error on send log' . PHP_EOL;
				var_dump($error);
			}
		});


		$this->batch[$index] = [];
	}


	/**
	 * Datetime format used by the sender.
	 *
	 * @return string
	 */
	public function getDatetimeFormat() : string
	{
		return 'epoch';
	}


	/**
	 * Force UTC as timezone?
	 *
	 * @return bool
	 */
	public function forceUTCTimezone() : bool
	{
		return true;
	}

}
