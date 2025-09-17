<?php

/**
 * Class Model_Senders_Stdout
 *
 * Send logs to stdout.
 */
class Model_Senders_Stdout implements Model_Contracts_Sender
{


    /**
     * Model_LogSender constructor.
     *
     * @param Model_Contracts_Client $client
     * @param bool $async
     */
    public function __construct($client, bool $async = false)
    {}


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
        echo $index . "\n";
        echo json_encode($body) . "\n";
        echo "=============================================\n";
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
