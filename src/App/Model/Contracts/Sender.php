<?php


/**
 * Interface Sender.
 */
interface Model_Contracts_Sender
{

	/**
	 * Model_Contracts_Sender constructor.
	 *
	 * @param mixed $client
	 * @param bool $async
	 */
	public function __construct($client, bool $async = false);


	/**
	 * Send log entry.
	 *
	 * @param string $index
	 * @param array $body
	 * @return mixed
	 */
	public function send(string $index, array $body);


	/**
	 * Get datetime format used by the sender.
	 *
	 * @return string
	 */
	public function getDatetimeFormat() : string;


	/**
	 * Force UTC as timezone.
	 *
	 * @return mixed
	 */
	public function forceUTCTimezone() : bool;
}
