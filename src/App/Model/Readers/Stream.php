<?php

/**
 * Class Model_Readers_Stream.
 *
 * Stream reader
 */
class Model_Readers_Stream implements Model_Contracts_Reader
{


	/**
	 * File path.
	 *
	 * @var string
	 */
	protected $file;


	/**
	 * Maximum line length.
	 *
	 * @var int
	 */
	protected $line_length;


	/**
	 * File handler.
	 *
	 * @var resource
	 */
	protected $fp;


	/**
	 * Last pointer in file (byte).
	 *
	 * @var int
	 */
	protected $last_stat =
	[
		'pos'       => 0,
		'size'      => 0
	];


	/**
	 * Last time that file was checked (In seconds).
	 *
	 * @var int
	 */
	protected $last_time = 0;


	/**
	 * Check interval.
	 *
	 * @var int
	 */
	protected $interval;


	/**
	 * Model_StreamReader constructor.
	 *
	 * @param string $file          File path
	 * @param float  $interval      Read interval
	 * @param int    $line_length   Maximum line length in bytes
	 * @throws Exception
	 */
	public function __construct(string $file, $interval = 0.5, int $line_length = 32768)
	{
		$this->line_length = $line_length;
		$this->interval    = $interval;
		$this->file        = $file;

	}


	/**
	 * Destructor.
	 */
	public function __destruct()
	{
		// Detach watcher
        if ($this->fp)
		    @fclose($this->fp);
	}


	/**
	 * Return file lines.
	 *
	 * @return bool|Generator
	 * @throws Exception
	 */
	public function getLines()
	{

		$now = microtime(true);

		// Ignore when read interval was not accomplished
		if (($this->last_time + $this->interval) > $now)
			return false;

		$this->last_time = $now;

		$stat = null;

		// Check file stats
        if ($this->fp)
            $stat = @fstat($this->fp);

		// Check if file is still linked/exists
		if (empty($stat['nlink']))
		{
			// Try to attach new file
			$this->attachFile();
			return false;
		}

		// Set new position in case that file is truncated (or replaced)
		if ($stat['size'] < $this->last_stat['pos'])
		{
			$this->last_stat['pos'] = 0;
			rewind($this->fp);
		}

		// Read list
		while ($line = stream_get_line($this->fp, $this->line_length, PHP_EOL))
			yield $line;

		// Set last status
		$this->last_stat['pos']  = ftell($this->fp);
		$this->last_stat['size'] = $stat['size'];

		return false;
	}


	/**
	 * Attach/Open file
	 *
	 * @return bool
	 */
	protected function attachFile($eof = true) : bool
	{
	    if ($this->fp)
		    @fclose($this->fp);

		if (($this->fp = @fopen($this->file, 'r')) === false)
			return false;

		// Avoid block stream
		if (!@stream_set_blocking($this->fp, 0))
			return false;

		// Move to end of file
		if ($eof)
		{
			fseek($this->fp, -1, SEEK_END);
			$this->last_stat['pos'] = ftell($this->fp);
		}
		else
			$this->last_stat['pos'] = 0;

		return true;
	}


}
