<?php

/**
 * Parameters helper class
 *
 * @package     Mamuph Params Helper
 * @category    Helpers
 * @author      Mamuph Team
 * @copyright   (c) 2015-2016 Mamuph Team
 *
 */
abstract class Core_Params
{

    /**
     * @var string  Current executable name
     */
    public static $executable;


    /**
     * @var array   Argument list and their values after to be processed
     */
    protected static $definitions;


    /**
     * @var array   List of collected unknown arguments
     */
    protected static $unknown_arguments = [];


    /**
     * Process the argument list according to the arguments definition
     *
     * @param array $definitions The argument definition list
     */
    public static function process(array $definitions)
    {

        Params::$definitions = $definitions;

        // Get raw arguments
        $args = $GLOBALS['argv'];

        // Extract executable name
        Params::$executable = $args[0];
        array_shift($args);

        $free_def_sort = 0;

        foreach ($args as $ka => $arg)
        {

            $found = null;

            if (strpos($arg, '-') === 0)
            {
                $found = Params::searchDefinition($arg);
            }
            else
            {
                $found = Params::searchFreeDefinition($arg, $free_def_sort);
                $free_def_sort++;
            }

            if ($found)
            {
                Params::$definitions = Arr::merge(Params::$definitions, $found);
            }
            else
            {
                Params::$unknown_arguments[] = $arg;
            }

        }

    }


    /**
     * Search the definition that match with short_arg or long_arg
     *
     * @param string $arg Argument name with hyphens
     * @return array|bool
     */
    protected static function searchDefinition(string $arg)
    {

        $type = null;

        if (strpos($arg, '--') === 0)
        {
            $type = 'long_arg';
            $arg = substr($arg, 2);
        }
        else if (strpos($arg, '-') === 0)
        {
            $type = 'short_arg';
            $arg = substr($arg, 1);
        }

        // Split value
        $value = null;

        if (strpos($arg, '=') !== false)
        {
            list($arg, $value) = explode('=', $arg, 2);
        }

        // Search argument in definition list
        $found = array_filter(Params::$definitions, function ($passed_definition) use ($arg, $type) {
          return !empty($passed_definition[$type]) && $passed_definition[$type] === $arg;
        });

        if (empty($found))
        {
            return false;
        }

        $found[key($found)]['value'] = empty($value) ? true : $value;

        return $found;
    }


    /**
     * Search free definitions
     *
     * @param $arg
     * @param int $free_def_sort
     * @return array|bool
     */
    protected static function searchFreeDefinition($arg, int $free_def_sort = 0)
    {

        $current_def = -1;

        // Search argument in definition list
        $found = array_filter(Params::$definitions,
          function ($passed_definition) use ($free_def_sort, &$current_def) {

              if (isset($passed_definition->short_arg) || isset($passed_definition->long_arg))
              {
                  return false;
              }

              $current_def++;

              if ($free_def_sort != $current_def)
              {
                  return false;
              }

              return true;
          });


        if (empty($found))
        {
            return false;
        }

        $found[key($found)]['value'] = $arg;

        return $found;
    }


    /**
     * Get parameter value
     *
     * @param string $param
     * @return array|bool
     */
    public static function get(string $param = null)
    {

        if (empty($param))
        {
            return Params::$definitions;
        }

        if (!isset(Params::$definitions[$param]) || !isset(Params::$definitions[$param]['value']))
        {
            return false;
        }

        return Params::$definitions[$param]['value'];
    }


    /**
     * Set a parameter value outside the command line
     *
     * @param string $param The parameter name
     * @param string $value The parameter value
     * @return string
     */
    public static function set(string $param, string $value) : string
    {
        return Params::$definitions[$param]['value'] = $value;
    }


    /**
     * Perform a validation
     *
     * @return array|false
     */
    public static function validate()
    {
        $errors = [];

        foreach (Params::$definitions as $k => $definition)
        {

            // Check non optional parameters
            if (isset($definition['optional'])
              && $definition['optional'] === false
              && !isset($definition['value']))
            {
                $errors[$k] = 'Not passed';
                continue;
            }

            // Check value type
            if (isset($definition['value']) && isset($definition['accept_value']))
            {

                $value = $definition['value'];

                switch ($definition['accept_value'])
                {
                    case 'alnum':
                    case 'alphanumeric':

                        if (!ctype_alnum($value))
                            $errors[$k] = 'Not alphanumeric';

                        break;

                    case 'alpha':
                    case 'alphabetic':

                        if (!ctype_alpha($value))
                            $errors[$k] = 'Not alphabetic';

                        break;

                    case 'int':
                    case 'integer':

                        if (!ctype_digit($value))
                            $errors[$k] = 'Not integer';

                        break;

                    case 'num':
                    case 'numeric':

                        if (!is_numeric($value))
                            $errors[$k] = 'Not integer';

                        break;

                    case 'list_lower':

                        $value              = Str::lower($value);
                        $definition['list'] = array_map('Str::lower', $definition['list']);

                        // Do not break on purpose

                    case 'list':

                        if (!in_array($value, $definition['list']))
                            $errors[$k] = 'Not in list';

                        break;
                }


            }


        }

        return empty($errors) ? false : $errors;

    }

}