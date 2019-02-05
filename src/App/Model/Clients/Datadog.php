<?php

use Amp\Artax\DefaultClient;

/**
 * Class Model_Clients_Datadog.
 */
class Model_Clients_Datadog extends Model_Client
{

	/**
	 * Maximum number of log events per request.
	 *
	 * @see https://docs.datadoghq.com/api/?lang=bash#send-logs-over-http
	 */
	const MAX_BATCH_SIZE = 50;


	/**
	 * Model_Clients_ElasticSearch constructor.
	 *
	 * @param array $hosts
	 * @param int $retries
	 * @param bool $check_cert
	 * @param int $batch_size
	 */
	public function __construct(array $hosts, int $retries = 2, bool $check_cert = true, int $batch_size = 30)
	{
		parent::__construct($hosts, $retries, $check_cert, $batch_size);

		// Set limit to the batch size
		$this->settings['batch_size'] = $this->settings['batch_size'] > static::MAX_BATCH_SIZE ? static::MAX_BATCH_SIZE : $this->settings['batch_size'];

		$this->client = new DefaultClient();
	}

}
