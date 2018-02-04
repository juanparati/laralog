<?php

/**
 * Class Model_LogSender.
 *
 * Send logs to Elastic Search.
 */
class Model_LogSender
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
     * @param \Elasticsearch\Client $client
     * @param bool $future_mode
     */
    public function __construct(\Elasticsearch\Client $client, $future_mode = false)
    {
        $this->client = $client;
        $this->future_mode = $future_mode;
    }


    /**
     * Send index to Elastic Search.
     *
     * @param string $index
     * @param array $body
     * @param string $type
     */
    public function send(string $index, array $body, string $type = 'log')
    {
        $payload = [
            'index' => $index,
            'type'  => $type,
            'body'  => $body
        ];

        // Set future mode
        if ($this->future_mode)
            $payload['client'] = ['future' => 'lazy'];

        if (!$this->is_repeated($payload))
        {
            $this->last_entry = $payload;
            $this->client->index($payload);
        }
    }


    /**
     * Check when current entry is repeated.
     *
     * @param $payload
     * @return bool
     */
    protected function is_repeated(array $payload) : bool
    {
        // @see: http://thinkofdev.com/equal-identical-and-array-comparison-in-php/
        return $this->last_entry == $payload;
    }

}