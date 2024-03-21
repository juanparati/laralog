<?php

use Amp\Http\Client\HttpClient;
use Amp\Http\Client\Request;


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
     * @var HttpClient
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
		$this->client_settings = $client->getClientSettings();
        $this->async = $async;
    }


    /**
     * Send log entry.
     *
     * @param string $index
     * @param array $body
     * @param string $type
     * @throws \Amp\Http\Client\HttpException
     */
    public function send(string $index, array $body)
    {
		$this->batch[$index][] = $body;

		if (count($this->batch[$index]) >= $this->client_settings['batch_size'])
			$this->sendNow($index);
    }

    /**
     * Perform the request.
     *
     * @param Request $request
     * @return void
     * @throws \Amp\Http\Client\HttpException
     */
    protected function performRequest(Request $request)
    {
        $response = $this->client->request($request);

        if (Params::get('verbose') && !$response->isSuccessful()) {
            echo 'Error on send log ' . $response->getStatus();
        }
    }


    /**
     * Send logs immediately.
     *
     * @param string $index
     * @throws \Amp\Http\Client\HttpException
     */
    protected function sendNow(string $index)
	{
		$query_params = http_build_query([
			'ddsource' => 'log',
			'service'  => $index,
			'hostname' => $this->batch[$index][0]['hostname']
		]);

		$request = new Request(
            $this->client_settings['hosts'][0] . '?' . $query_params,
            'POST',
            json_encode($this->batch[$index])
        );
        $request->setHeader('Content-Type', 'application/json');

        if ($this->async) {
            Amp\async(fn() => $this->performRequest($request));
        } else {
            $this->performRequest($request);
        }

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
