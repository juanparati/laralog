<?php
use PHPUnit\Framework\TestCase;


class TestSystemFlatDB extends TestCase
{

    private static $resources = 'Resources{{DS}}FlatDBTest';


    protected $test_data =
    [
        'foo'       => 'bar',
        'level1'    =>
            [
                'level2' => 'bar'
            ]
    ];


    public static function setUpBeforeClass()
    {
        self::$resources = TESTPATH . self::$resources;
        self::$resources = str_replace('{{DS}}', DS, self::$resources);

        @unlink(self::$resources . DS . 'db.ser');
        @unlink(self::$resources . DS . 'db.json');
    }


    /**
     * Attach drivers before every test
     */
    public function setUp()
    {
        FlatDB::instance('serialize')->attach(new FlatDB_Serialize(self::$resources . DS . 'db.ser'));
        FlatDB::instance('json')->attach(new FlatDB_JSON(self::$resources . DS . 'db.json'));
    }


    /**
     * Detach drivers after every test
     */
    public function tearDown()
    {
        // Detach
        FlatDB::instance('serialize')->detach();
        FlatDB::instance('json')->detach();
    }


    /**
     * Test write
     *
     * @group   mamuph.system.flatdb
     */
    public function test_write()
    {
        // Write using the serialization driver
        FlatDB::instance('serialize')->write(Arr::merge($this->test_data, ['driver' => 'serialize']));
        $this->assertNotEquals(false, FlatDB::instance('serialize')->flush());

        // Write using the JSON driver
        FlatDB::instance('json')->write(Arr::merge($this->test_data, ['driver' => 'json']));
        $this->assertNotEquals(false, FlatDB::instance('json')->flush());

    }


    /**
     * Test read
     *
     * @depends test_write
     * @group   mamuph.system.flatdb
     */
    public function test_read()
    {

        // Read the full array
        $this->assertEquals(Arr::merge($this->test_data, ['driver' => 'serialize']), FlatDB::instance('serialize')->read());
        $this->assertEquals(Arr::merge($this->test_data, ['driver' => 'json']), FlatDB::instance('json')->read());

        // Read specific key
        $this->assertEquals('serialize', FlatDB::instance('serialize')->read('driver'));
        $this->assertEquals('bar'      , FlatDB::instance('serialize')->read('level1.level2'));
        $this->assertEquals('json'     , FlatDB::instance('json')     ->read('driver'));
        $this->assertEquals('bar'      , FlatDB::instance('json')     ->read('level1.level2'));

    }



}