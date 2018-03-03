<?php


class Model_STDINReader implements Model_Contracts_Reader
{

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