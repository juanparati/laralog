<?php
use PHPUnit\Framework\TestCase;


/**
 * Main test group
 */
class TestsSystemHook extends TestCase
{


    /**
     * Helper var
     *
     * @var bool
     */
    protected $callable_result = false;


    /**
     * Callable mock function
     */
    public function callable_function($arg)
    {
        $this->callable_result = $arg;
    }


    public static function setUpBeforeClass()
    {
        // Initialize Hook
        Hook::instance();
    }


    /**
     * Test if single parameter is accepted
     *
     * @group   mamuph.system.hook
     */
    public function test_single_hook()
    {

        // Hook ID
        $hookid = uniqid();

        $my_event1_result = false;
        $my_event2_result = false;

        // Set first custom hook
        Hook::instance()->attach('MY_EVENT1', function() use (&$my_event1_result)
        {
            $my_event1_result = true;
        });

        // Set second custom hook
        Hook::instance()->attach('MY_EVENT2', function($args) use (&$my_event2_result)
        {
            $my_event2_result = $args;

        },
        $hookid);


        // Check if hooks are attached
        $this->assertTrue(Hook::instance()->wasAttached('MY_EVENT1'));
        $this->assertTrue(Hook::instance()->wasAttached('MY_EVENT2', $hookid));
        $this->assertFalse(Hook::instance()->wasAttached($hookid));

        // Notify
        Hook::instance()->notify('MY_EVENT1');
        Hook::instance()->notify('MY_EVENT2', $hookid);

        $this->assertTrue($my_event1_result);
        $this->assertEquals($my_event2_result, $hookid);

    }


    /**
     * Test detach and re-attach hook
     *
     * @depends test_single_hook
     * @group mamuph.system.hook
     */
    public function test_reattach_hook()
    {

        $arg = uniqid();

        // Detach previous event "MY_EVENT1"
        Hook::instance()->attach('MY_EVENT1', [$this, 'callable_function']);

        // Check that callable_result remains as false
        $this->assertFalse($this->callable_result);

        // Notify
        Hook::instance()->notify('MY_EVENT1', $arg);

        $this->assertEquals($this->callable_result, $arg);

    }


    /**
     * Test multiple listeners
     *
     * @group mamuph.system.hook
     */
    public function test_multiple_listeners()
    {
        $callable_result = ['first' => false, 'second' => false];

        // Set first event listener
        $hookid1 = uniqid();

        Hook::instance()->attach('MULTIPLE', function($args) use (&$callable_result)
        {
            $callable_result['first'] = $args;
        },
        $hookid1);


        // Set second event listener
        $hookid2 = uniqid();

        Hook::instance()->attach('MULTIPLE', function($args) use (&$callable_result)
        {
            $callable_result['second'] = $args;
        },
        $hookid2);


        // Notify
        Hook::instance()->notify('MULTIPLE', true);

        $this->assertEquals($callable_result, ['first' => true, 'second' => true]);

    }


    /**
     * Test UNIX signal hook
     *
     * @group mamuph.system.hook
     */
    public function test_unix_signal()
    {

        $alarm_status = false;

        // Execute test only if PCNTL is loaded
        if (function_exists('pcntl_signal') && function_exists('pcntl_alarm'))
        {
            Hook::instance()->attach('UNIX_SIGALRM', function() use (&$alarm_status)
            {
               $alarm_status = true;
            });

            // Send alarm signal like a Pro
            pcntl_alarm(1);

            // Non-blocking version of the sleep function
            // I do this because sleep has an unexpected behaviour with HHVM.
            // I feel like a VW engineer cheating with the emissions tests.
            Apprunner::sleep(3);

        }

        $this->assertTrue($alarm_status);

    }

}