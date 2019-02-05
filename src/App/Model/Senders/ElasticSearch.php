<?php

/**
 * Class Model_Sender_ElasticSearch
 *
 * Send logs to Elastic Search.
 */
class Model_Senders_ElasticSearch implements Model_Contracts_Sender
{

    /**
     * Elastic Search client.
     *
     * @var \Elasticsearch\Client
     */
    protected $client;


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
    protected $future_mode = false;


    /**
     * Model_LogSender constructor.
     *
     * @param Model_Contracts_Client $client
     * @param bool $async
     */
    public function __construct($client, bool $async = false)
    {
        $this->client = $client->getClient();
        $this->future_mode = $async;
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
        $payload = [
            'index' => $index,
            'type'  => 'log',
            'body'  => $body
        ];

        // Set future mode
        if ($this->future_mode)
            $payload['client'] = ['future' => 'lazy'];

        $this->client->index($payload);
    }


	/**
	 * Datetime format used by the sender.
	 *
	 * @return string
	 */
	public function getDatetimeFormat() : string
	{
		return 'Y-m-d\TH:i:sP';
	}


	/**
	 * Force UTC as timezone?
	 *
	 * @return bool
	 */
	public function forceUTCTimezone(): bool
	{
		return false;
	}


}
