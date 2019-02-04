<?php


/**
 * Interface Model_Contracts_Client.
 */
interface Model_Contracts_Client
{


	/**
	 * Model_Contracts_Client constructor.
	 *
	 * @param array $hosts
	 * @param int $retries
	 * @param bool $check_cert
	 * @param int $batch_size
	 */
	public function __construct(
		array $hosts,
		int $retries = 2,
		bool $check_cert = true,
		int $batch_size = 30
	);


	/**
	 * Get client instance.
	 *
	 * @return mixed
	 */
	public function getClient();


	/**
	 * Get client settings.
	 *
	 * @return array
	 */
	public function getClientSettings() : array;

}
