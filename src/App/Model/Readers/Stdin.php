<?php


/**
 * Class Model_Readers_Stdin.
 */
class Model_Readers_Stdin implements Model_Contracts_Reader
{

	/**
	 * Model_Readers_Stdin constructor.
	 */
	public function __construct()
	{
		@stream_set_blocking(STDIN, 0);
	}


	/**
     * Get lines from STDIN
     *
     * @return Generator|mixed
     */
    public function getLines()
    {
        while ($line = fgets(STDIN)) {
            yield $line;
        }
    }
}
