<?php
use PHPUnit\Framework\TestCase;


class TestSystemMem extends TestCase
{


    /**
     * Test Mem::convert
     *
     * @group mamuph.system.mem
     */
    public function test_mem_convert()
    {
        $this->assertEquals(0       , Mem::convert(0));
        $this->assertEquals(1       , Mem::convert(1024, 'k'));
        $this->assertEquals(1       , Mem::convert('1024 bytes', 'KB'));
        $this->assertEquals(10      , Mem::convert('10240KB', 'Megas'));
        $this->assertEquals(0.02441 , Mem::convert('25MB', 'GB'), '', 0.0001);
        $this->assertEquals(25600   , Mem::convert('25k'));
    }

}