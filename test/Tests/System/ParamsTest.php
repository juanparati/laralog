<?php
use PHPUnit\Framework\TestCase;


/**
 * Main test group
 */
class TestsSystemParams extends TestCase
{

    private static $argv_val;


    /**
     * Perform a copy of parameters passed to PHPUnit.
     */
    public static function setUpBeforeClass()
    {
        global $argv;

        self::$argv_val = $argv;
    }


    /**
     * Restore the argument values as soon that this test suit is finished.
     */
    public static function tearDownAfterClass()
    {
        global $argv;

        $argv = self::$argv_val;
    }


    /**
     * Delete phpunit arguments in order to avoid that Param tests
     * are not polluted.
     */
    public function tearDown()
    {
        global $argv;

        // Delete arguments passes to phpunit
        $argv    = [];
        $argv[0] = __FILE__;
    }


    /**
     * Test if single parameter is accepted.
     *
     * @group   mamuph.system.params
     */
    function test_params_single()
    {

        // Set arguments
        global $argv;

        // Mock arguments
        $argv[1] = '-a';
        $argv[2] = '-b';
        $argv[3] = '-c';

        // Process arguments
        Params::process(
            [
                'alpha' =>
                [
                    'short_arg'     => 'a',
                    'optional'      => true
                ],
                'beta' =>
                [
                    'short_arg'     => 'b',
                    'optional'      => true
                ]
            ]
        );

        // Validation
        $this->assertTrue(Params::get('alpha') && Params::get('beta'));

    }


    /**
     * Test if long parameters are accepted.
     *
     * @group   mamuph.system.params
     */
    function test_long_parameters()
    {

        // Set arguments
        global $argv;

        // Mock arguments
        $argv[1] = '--foo';
        $argv[2] = '--bar';
        $argv[3] = '-c';

        // Process arguments
        Params::process(
            [
                'alpha' =>
                    [
                        'long_arg'     => 'foo',
                        'optional'     => true
                    ],
                'beta' =>
                    [
                        'long_arg'     => 'bar',
                        'optional'     => true
                    ]
            ]
        );

        // Validation
        $this->assertTrue(Params::get('alpha') && Params::get('beta'));

    }


    /**
     * Test if validation report is correct when a missing extra parameter is given.
     *
     * @group   mamuph.system.params
     */
    public function test_missing_extra_parameter()
    {

        // Set arguments
        global $argv;

        // Mock arguments
        $argv[1] = '--foo';
        $argv[2] = 'example';

        // Process arguments
        Params::process(
            [
                'alpha' =>
                    [
                        'long_arg'     => 'foo',
                        'accept_value' => 'string',
                        'optional'     => false
                    ]
            ]
        );

        // Validation
        $this->assertTrue(Params::get('alpha') && !Params::get('beta'));

    }


    /**
     * Test parameters evaluation when values are passed.
     *
     * @group   mamuph.system.params
     */
    public function test_value_parameters()
    {

        // Set arguments
        global $argv;

        // Mock arguments
        $argv[1] = '/foo/bar';
        $argv[2] = '--gamma-par=bar';

        // Process arguments
        Params::process(
            [
                'alpha' =>
                    [
                        'accept_value' => 'string',
                        'optional'     => false
                    ],
                'beta'  =>
                    [
                        'long_arg'     => 'beta',
                        'accept_value' => 'string',
                        'optional'     => false

                    ],
                'gamma-par' =>
                    [
                        'long_arg'      => 'gamma-par',
                        'accept_value'  => 'string',
                        'optional'      => 'false'
                    ]
            ]
        );


        // Validation
        $this->assertArrayHasKey('beta', Params::validate());
        $this->assertEquals(Params::get('alpha'), $argv[1] );
        $this->assertFalse (Params::get('beta')            );
        $this->assertEquals(Params::get('gamma-par'), 'bar');

    }


    /**
     * Test if multiple free arguments are evaluated.
     *
     * @group   mamuph.system.params
     */
    public function test_free_parameters()
    {
        // Set arguments
        global $argv;

        // Mock arguments
        $argv[1] = 'argument1';
        $argv[2] = 'argument2';

        // Process arguments
        Params::process(
            [
                'alpha' =>
                    [
                        'accept_value'  => 'string',
                        'optional'      => false
                    ],
                'beta'  =>
                    [
                        'accept_value'  => 'string',
                        'optional'      => false
                    ]
            ]
        );

        $this->assertEquals(Params::get('alpha'), $argv[1]);
        $this->assertEquals(Params::get('beta') , $argv[2]);

    }


    /**
     * Test set params.
     *
     * @group   mamuph.system.params
     */
    public function test_set_params()
    {

        // Set arguments
        global $argv;

        // Mock arguments
        $argv[1] = 'argument1';
        $argv[2] = '--beta-x=argument2';

        // Process arguments
        Params::process(
            [
                'alpha' =>
                    [
                        'accept_value'  => 'string',
                        'optional'      => false
                    ],
                'beta'  =>
                    [
                        'long_arg'      => 'beta-x',
                        'accept_value'  => 'string',
                        'optional'      => false
                    ],
                'gamma' =>
                    [
                        'short_arg'     => 'gamma',
                        'accept_value'  => 'string',
                        'optional'      => true
                    ]
            ]
        );

        $this->assertEquals(Params::get('alpha'), $argv[1]  );
        $this->assertEquals(Params::get('beta'), 'argument2');
        $this->assertFalse (Params::get('gamma')            );

        Params::set('alpha', 'one'  );
        Params::set('beta' , 'two'  );
        Params::set('gamma', 'three');

        $this->assertEquals(Params::get('alpha'), 'one'  );
        $this->assertEquals(Params::get('beta') , 'two'  );
        $this->assertEquals(Params::get('gamma'), 'three');

    }


    /**
     * Test set params.
     *
     * @group   mamuph.system.params
     */
    public function test_param_types()
    {

        // Set arguments
        global $argv;

        // Mock arguments
        $argv[1] = '--alnum=13HelloWorld';
        $argv[2] = '--alpha=HelloWorld';
        $argv[3] = '-int=3123';
        $argv[3] = '--num=3.141592';
        $argv[4] = '--list=Two';
        $argv[5] = '--any=Whatever13_22';

        // Process arguments
        Params::process(
          [
            'alnum' =>
              [
                'long_arg'     => 'alnum',
                'accept_value' => 'alnum',
                'optional'     => false,
              ],
            'alpha' =>
              [
                'long_arg'     => 'alpha',
                'accept_value' => 'alpha',
                'optional'     => false,
              ],
            'int'   =>
              [
                'short_arg'    => 'int',
                'accept_value' => 'int',
                'optional'     => true
              ],
            'num'   =>
              [
                'long_arg'     => 'num',
                'accept_value' => 'num',
                'optional'     => true
              ],
            'list'  =>
              [
                'long_arg'     => 'list',
                'accept_value' => 'list',
                'list'         => ['One', 'Two', 'Three'],
                'optional'     => false,
              ],
            'any'   =>
              [
                'long_arg'     => 'any',
                'accept_value' => 'any',
                'optional'     => false,
              ],
          ]
        );

        $this->assertEmpty(Params::validate());

        // Test wrong types
        Params::set('alnum', '12Test_Invalid');
        $this->assertArrayHasKey('alnum', Params::validate());

        Params::set('alpha', '13.141592');
        $this->assertArrayHasKey('alpha', Params::validate());

        Params::set('int', '13.141592');
        $this->assertArrayHasKey('alpha', Params::validate());

        Params::set('num', '13Test');
        $this->assertArrayHasKey('num', Params::validate());

        Params::set('list', '13Test');
        $this->assertArrayHasKey('list', Params::validate());

    }

}