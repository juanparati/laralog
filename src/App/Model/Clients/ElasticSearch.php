<?php

use Elasticsearch\ClientBuilder;


/**
 * Class Model_Clients_ElasticSearch.
 */
class Model_Clients_ElasticSearch extends Model_Client
{

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

		$this->client = ClientBuilder::create()
			->setHosts($hosts)
			->setRetries($retries)
			->setSSLVerification($check_cert)
			->setHandler(ClientBuilder::defaultHandler(['max_handles' => $batch_size]))
			->build();
	}

}
