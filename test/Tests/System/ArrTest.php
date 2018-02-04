<?php
use PHPUnit\Framework\TestCase;


class TestSystemArr extends TestCase
{


    /**
     * Test Arr::is_assoc
     *
     * @group mamuph.system.arr
     */
    public function test_is_associative()
    {
        $this->assertTrue(Arr::isAssoc(array('foo' => 'bar')));
        $this->assertFalse(Arr::isAssoc(array('foo', 'bar')));
    }


    /**
     * Test Arr::is_array
     *
     * @group mamuph.system.arr
     */
    public function test_is_array()
    {
        $this->assertTrue(Arr::isArray(array('foo')));
        $this->assertFalse(Arr::isArray(new stdClass()));
    }


    /**
     * Test Arr::callback
     *
     * @group mamuph.system.arr
     */
    public function test_callback()
    {
        // Get the callback function and parameters
        list($func, $params) = Arr::callback('Foo::my_bar(apple,orange,1,2)');

        $this->assertEquals(['Foo', 'my_bar'], $func);
        $this->assertEquals(['apple', 'orange', '1', '2'], $params);

    }


    /**
     * Test Arr::path and Arr::set_path
     *
     * @group mamuph.system.arr
     */
    public function test_path()
    {
        $data = [
            'level1' =>
            [
                'level1a' =>
                [
                    'level1aa' => 'foo',
                    'level1ab' => 'bar',
                    'level1ac' => true,
                    'common'   => 'foobar1'
                ],
                'level1b' => true,
                'level1c' =>
                [
                    'common' => 'foobar2'
                ]

            ],
            'level2' =>
            [
                'level2a' => false,
                'level2b' => 10000
            ]
        ];

        $this->assertFalse(Arr::path($data, 'level2.level2a'));

        $this->assertTrue(Arr::path($data, 'level1|level1b', null, '|'));

        $this->assertEquals('Foobar', Arr::path($data, 'level1.level1a.levelX', 'Foobar'));

        $this->assertEquals(10000, Arr::path($data, ['level2', 'level2b']));

        $this->assertContains([
            'level1aa' => 'foo',
            'level1ab' => 'bar',
            'level1ac' => true,
            'common'   => 'foobar'
        ], Arr::path($data, 'level1.level1a'));

        $this->assertArraySubset(['foobar1', 'foobar2'], Arr::path($data, 'level1.*.common'));


        Arr::setPath($data, 'level2-level2c', true, '-');
        $this->assertTrue(Arr::path($data, 'level2.level2c', false));

        Arr::setPath($data, 'level1.level1a', 'foo');
        $this->assertEquals('foo', Arr::path($data, 'level1.level1a'));

    }


    /**
     * Test Arr::extract
     *
     * @group mamuph.system.arr
     */
    public function test_extract()
    {

        $data =
        [
            'level1' =>
            [
                'level2a' => 'value 1',
                'level2b' => 'value 2'
            ],
            'level2' => true
        ];


        $this->assertEquals(['level1' => ['level2a' => 'value 1'], 'level2' => true],
            Arr::extract($data, ['level1.level2a', 'level2']));


        $this->assertEquals(['level1' => ['level2b' => 'value 2'], 'foo' => null],
            Arr::extract($data, ['level1.level2b', 'foo']));

        $data = ['a' => 'a', 'b' => 'b', 'c' => 'c'];

        $this->assertEquals(['a' => 'a', 'c' => 'c'],
            Arr::extract($data, ['a', 'c']));

    }


    /**
     * Test Arr::pluck
     *
     * @group mamuph.system.arr
     */
    public function test_pluck()
    {

        $data =
        [
            'common' => 'foo',
            'level1' =>
            [
                'level1a' => 'foolevel1',
                'common'  => 'bar'
            ],
            'level2' =>
            [

                'common' => 'foo'
            ]
        ];


        $this->assertEquals(['bar', 'foo'], Arr::pluck($data, 'common'));
        $this->assertEquals(['foolevel1'], Arr::pluck($data, 'level1a'));
    }


    /**
     * Test Arr::flatten
     *
     * @group mamuph.system.arr
     */
    public function test_flatten()
    {

        $data =
        [
            'set' =>
            [
                'one' => 'something'
            ],
            'two' => 'other'
        ];

        $this->assertEquals(['one' => 'something', 'two' => 'other'], Arr::flatten($data));


        $data =
        [
            'level1' =>
            [
                'level2' =>
                [
                    'level3' => 'foo'
                ]
            ],
            'level1b' =>
            [
                'level2b' => 'bar'
            ]
        ];

        $this->assertEquals(['level3' => 'foo', 'level2b' => 'bar'], Arr::flatten($data));



    }


    /**
     * Test Arr::get
     *
     * @group mamuph.system.arr
     */
    public function test_get()
    {

        $data = [
            'foo'   => 'bar',
            'foo2'  => false
        ];


        $this->assertEquals('bar', Arr::get($data, 'foo'));
        $this->assertFalse(Arr::get($data, 'foo2'));
        $this->assertEquals('bar', Arr::get($data, 'any', 'bar'));

    }


    /**
     * Test Arr::map
     *
     * @group mamuph.system.arr
     */
    public function test_map()
    {


        $data =
        [
            '<h1>Foo</h1>',
            '<h1>Bar</h1>'
        ];

        $this->assertEquals(['Foo', 'Bar'], Arr::map('strip_tags', $data));

        $data =
        [
            'level1' => '<h1>one Foo</h1>',
            'level2' =>
            [
                'level2b' => '<h1>one Bar</h1>'
            ]

        ];


        $this->assertEquals(
        [
            'level1' => 'Foo',
            'level2' =>
            [
                'level2b' => 'Bar'
            ]
        ], Arr::map(['strip_tags', [$this, 'map_mockup']], $data, ['level1']));

    }


    /**
     * Test Arr::merge
     *
     * @group mamuph.system.arr
     */
    public function test_merge()
    {

        $john =
        [
            'name'      => 'john',
            'children'  =>
            [
                'fred',
                'paul',
                'sally',
                'jane'
            ]
        ];

        $mary =
        [
            'name' => 'mary',
            'children' => ['jane']
        ];


        $this->AssertEquals(
        [
            'name'      => 'mary',
            'children'  => [
                'fred',
                'paul',
                'sally',
                'jane']
        ], Arr::merge($john, $mary));

    }


    /**
     * Test Arr::overwrite
     *
     * @group mamuph.system.arr
     */
    public function test_overwrite()
    {
        $a1 = ['name' => 'john', 'mood' => 'happy', 'food' => 'bacon'];
        $a2 = ['name' => 'jack', 'food' => 'tacos', 'drink' => 'beer'];

        // Overwrite the values of $a1 with $a2
        $this->AssertEquals([
            'name' => 'jack',
            'mood' => 'happy',
            'food' => 'tacos'
        ], Arr::overwrite($a1, $a2));

    }


    /**
     * Mockup function used for test Arr::map
     *
     * @param $value
     * @return string
     */
    public function map_mockup($value)
    {
        return trim(str_replace('one', '', $value));
    }


}