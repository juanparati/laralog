<?php
// It is required for IPC signaling
if (function_exists('pcntl_async_signals')) {
    pcntl_async_signals(true);
} else {
    // @deprecated in favour of pctnl_async_signals
    declare(ticks = 1);
}



/**
 * Hook controller class based in the observed pattern.
 *
 * @package     Mamuph Hooks
 * @category    Hook
 * @author      Mamuph Team
 * @copyright   (c) 2015-2018 Mamuph Team
 */
abstract class Core_Hook
{


    /**
     * @var  Hook  Singleton instance container
     */
    protected static $_instance = [];


    /**
     * Hooks for unix signals and mamuph core events
     *
     * @see http://man7.org/linux/man-pages/man7/signal.7.html
     * @var array   Hook list
     */
    protected $hooks = [
        'IPC_SIGHUP'          => [],
        'IPC_SIGINT'          => [],
        'IPC_SIGQUIT'         => [],
        'IPC_SIGILL'          => [],
        'IPC_SIGTRAP'         => [],
        'IPC_SIGABRT'         => [],
        //'IPC_SIGIOT'          => [],
        'IPC_SIGBUS'          => [],
        'IPC_SIGFPE'          => [],
        //'IPC_SIGKILL'         => [],
        'IPC_SIGUSR1'         => [],
        'IPC_SIGSEGV'         => [],
        'IPC_SIGUSR2'         => [],
        'IPC_SIGPIPE'         => [],
        'IPC_SIGALRM'         => [],
        'IPC_SIGTERM'         => [],
        'IPC_SIGSTKFLT'       => [],
        'IPC_SIGCLD'          => [],
        'IPC_SIGCHLD'         => [],
        'IPC_SIGCONT'         => [],
        //'IPC_SIGSTOP'         => [],
        'IPC_SIGTSTP'         => [],
        'IPC_SIGTTIN'         => [],
        'IPC_SIGTTOU'         => [],
        'IPC_SIGURG'          => [],
        'IPC_SIGXCPU'         => [],
        'IPC_SIGXFSZ'         => [],
        'IPC_SIGVTALRM'       => [],
        'IPC_SIGPROF'         => [],
        'IPC_SIGWINCH'        => [],
        'IPC_SIGPOLL'         => [],
        'IPC_SIGIO'           => [],
        'IPC_SIGPWR'          => [],
        'IPC_SIGSYS'          => [],
        'IPC_SIGBABY'         => [],

        'MAMUPH_INITIALIZED'   => [],
        'MAMUPH_TERMINATED'    => []
    ];



    /**
     * Core_Hook constructor.
     *
     * @param   bool    $attach_signals     Attach UNIX signals as hooks into this instance
     */
    public function __construct(bool $attach_signals = true)
    {

        // @link http://www.ucs.cam.ac.uk/docs/course-notes/unix-courses/Building/files/signals.pdf
        if ($attach_signals && function_exists('pcntl_signal'))
        {

            foreach (array_keys($this->hooks) as $hookname)
            {

                if (strpos($hookname, 'IPC_') !== false)
                {
                    $signal = str_replace('IPC_', '', $hookname);

                    if (defined($signal))
                        pcntl_signal(constant($signal), [$this, '_notifySignal']);

                }
            }
        }

    }


    /**
     * Get the singleton instance of this class.
     *
     * @example
     *
     *     $hook = Hook::instance();
     *
     * @param   string  $name   Instance name
     * @param   bool    $attach_signals
     * @return  Hook
     */
    public static function instance(string $name = 'default', bool $attach_signals = true)
    {
        if (empty(Hook::$_instance[$name]))
        {
            // Create a new instance
            Hook::$_instance[$name] = new Hook($attach_signals);
        }

        return Hook::$_instance[$name];
    }


    /**
     * Notify UNIX signal.
     *
     * Note: This function should be called by the notification method.
     * Do no call this function unless that observes are called manually.
     *
     * @param   int $signal
     * @return  void
     * @throws \Exception
     */
    public function _notifySignal(int $signal) : void
    {
        foreach (array_keys($this->hooks) as $hookname)
        {

            if (strpos($hookname, 'IPC_') !== false)
            {
                $signal_cons = str_replace('IPC_', '', $hookname);

                if (defined($signal_cons) && constant($signal_cons) === $signal)
                {
                    $this->notify($hookname, $signal);
                    break;
                }

            }
        }
    }


    /**
     * Attach an observer to the hook
     *
     * @example
     *
     *      Hook::instance()->attach('MY_EVENT', function($args) { echo "Hey, event raised"; }, 'my_event');
     *
     *      // Or call method from current class
     *
     *      Hook::instance()->attach('MY_EVENT', array($this, 'raise_event'), 'my_event');
     *
     *      // Or call a single function
     *
     *      Hook::instance()->attach('MY_EVENT', 'raise_event', 'my_event');
     *
     * @param string    $hookname   The hook name
     * @param string|callable $method   Method that is called when the hook is raised
     * @param mixed     $id     The attachment ID, by default a random unique ID is assigned by default when false
     * @return void
     */
    public function attach(string $hookname, $method, $id = false) : void
    {
        $this->add($hookname);

        if ($id === false)
            $id = uniqid('event_', true);

        $this->hooks[$hookname][$id] = $method;
    }


    /**
     * Detach an observer from a hook
     *
     * @example
     *
     *      // Detach all the observes from a specific hook
     *      Hook::instance()->detach('MY_EVENT');
     *
     *      // Detach an observer from a specific hook
     *      Hook::instance()->detach('MY_EVENT', 'my_event');
     *
     *      // Detach one or more observers from a specific hook using the observer method as reference
     *      Hook::instance()->detach('MY_EVENT', null, 'raise_event');
     *
     * @param   string            $hookname   The hook name
     * @param   string            $id         The attachment ID (Optional)
     * @param   string|callable   $method     The method name (Optional)
     * @return  bool     True when one or more observers are detached
     */
    public function detach(string $hookname, string $id = null, $method = null) : bool
    {

        if (array_key_exists($hookname, $this->hooks))
        {

            if (empty($id) && empty($method))
            {
                $this->hooks[$hookname] = [];
                return true;
            }

            if (!empty($id))
            {
                if (array_key_exists($id, $this->hooks[$hookname]))
                {
                    unset($this->hooks[$hookname][$id]);
                    return true;
                }
            }

            $success = false;

            foreach ($this->hooks[$hookname] as $id => $observer)
            {

                if (is_array($observer))
                {
                    if (array_values($observer) === $method)
                    {
                        unset($this->hooks[$hookname][$id]);
                        $success = true;
                    }

                }
                else if ($observer === $method)
                {
                    unset($this->hooks[$hookname][$id]);
                    $success = true;
                }

            }

            return $success;

        }

        return false;

    }



    /**
     * Check if hook exists and it has attached at least one observer
     *
     * @example
     *
     *      // Check if at least one observer is attached to a hook
     *      Hook::instance()->was_attached('MY_EVENT');
     *
     *      // Check if a specific observer is attached to a hook (Search by hook-observer ID)
     *      Hook::instance()->was_attached('MY_EVENT', 'my_event');
     *
     *      // Check if a specific observer is attached to a hook (Search by observer method)
     *      Hook::instance()->was_attached('MY_EVENT', null, 'raise_event');
     *
     * @param   string          $hookname
     * @param   string          $id
     * @param   string|callable $method
     * @return  bool
     */
    public function wasAttached(string $hookname, string $id = null, $method = null) : bool
    {

        if (array_key_exists($hookname, $this->hooks))
        {

            if (empty($id) && empty($method))
                return isset($this->hooks[$hookname]) && count($this->hooks[$hookname]);


            if (!empty($id))
                return isset($this->hooks[$hookname][$id]);

            $attached = false;

            foreach ($this->hooks[$hookname] as $id => $observer)
            {

                if (is_array($observer))
                {
                    if (array_values($observer) === $method)
                        $attached = true;
                }
                else if ($observer === $method)
                {
                    $attached = true;
                }

            }

            return $attached;

        }

        return false;

    }


    /**
     * Add a new hook to the hooklist
     *
     * @param   string  $hookname   Hook name
     * @return  bool    True when hook is added to the hooklist or false when hook was already added
     */
    public function add(string $hookname) : bool
    {

        if (!$this->exists($hookname))
        {
            $this->hooks[$hookname] = [];
            return true;
        }

        return false;
    }


    /**
     * Check if hook is available in the hooklist
     *
     * @param   string    $hookname
     * @return  bool
     */
    public function exists(string $hookname) : bool
    {
        return array_key_exists($hookname, $this->hooks);
    }


    /**
     * Notify or call the observers
     *
     * @example
     *
     *      Hook::instance()->notify('MY_EVENT', 'argument');
     *
     * @param   string    $hookname
     * @param   mixed     $parameters
     * @return  void
     * @throws  Exception
     */
    public function notify(string $hookname, $parameters = null) : void
    {
        if (array_key_exists($hookname, $this->hooks))
        {
            foreach ($this->hooks[$hookname] as $observer)
                call_user_func($observer, $parameters);
        }
    }

}
