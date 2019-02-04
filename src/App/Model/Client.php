<?php
declare(strict_types=1);


/**
 * Class Model_Client.
 */
abstract class Model_Client implements Model_Contracts_Client
{

	/**
	 * @var array
	 */
	protected $settings;


	/**
	 * Client instance.
	 *
	 * @var mixed
	 */
	protected $client;


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
	)
	{
		$this->settings =
		[
			'hosts'         => $hosts,
			'retries'       => $retries,
			'no_check_cert' => $check_cert,
			'batch_size'    => $batch_size,
		];
	}


	/**
	 * Get the client instance.
	 *
	 * @return mixed
	 */
	public function getClient()
	{
		return $this->client;
	}


	/**
	 * Get client settings.
	 *
	 * @return array
	 */
	public function getClientSettings() : array
	{
		return $this->settings;
	}

}
