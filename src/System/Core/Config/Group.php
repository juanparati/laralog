<?php


/**
 * The group wrapper acts as an interface to all the config directives
 * gathered from across the system.
 *
 * This is the object returned from Core_Config::load
 *
 * Any modifications to configuration items should be done through an instance of this object
 *
 * @package    Mamuph Config
 * @category   Configuration
 * @author     Kohana Team and Mamuph Team
 * @copyright  (c) 2012-2014 Kohana Team
 * @license    http://kohanaframework.org/license
 */
abstract class Core_Config_Group extends ArrayObject implements Core_Config_Contract_Group
{

    /**
     * Reference the config object that created this group.
     * Used when updating config.
     *
     * @var Core_Contract_Config
     */
    protected $_parent_instance = null;


    /**
     * The group this config is for.
     * Used when updating config items.
     *
     * @var string
     */
    protected $_group_name = '';


    /**
     * Constructs the group object.
     * Core_Config passes the config group and its config items to the
     * object here.
     *
     * @param Core_Contract_Config $instance "Owning" instance of Core_Config
     * @param string $group The group name
     * @param array $config Group's config
     */
    public function __construct(
      Core_Contract_Config $instance,
      string $group,
      array $config = []
    ) {
        $this->_parent_instance = $instance;
        $this->_group_name = $group;

        parent::__construct($config, ArrayObject::ARRAY_AS_PROPS);
    }


    /**
     * Return the current group in serialized form.
     *
     * @example
     *
     *     echo $config;
     *
     * @return  string
     */
    public function __toString() : string
    {
        return serialize($this->getArrayCopy());
    }


    /**
     * Alias for getArrayCopy()
     *
     * @return array Array copy of the group's config
     */
    public function asArray() : array
    {
        return $this->getArrayCopy();
    }


    /**
     * Returns the config group's name
     *
     * @return string The group name
     */
    public function groupName() : string
    {
        return $this->_group_name;
    }


    /**
     * Get a variable from the configuration or return the default value.
     *
     * @example
     *
     *     $value = $config->get($key);
     *
     * @param   string $key array key
     * @param   mixed $default default value
     * @return  mixed
     */
    public function get(string $key, string $default = null)
    {
        return $this->offsetExists($key) ? $this->offsetGet($key) : $default;
    }


    /**
     * Sets a value in the configuration array.
     *
     * @example
     *
     *     $config->set($key, $new_value);
     *
     * @param   string $key array key
     * @param   mixed $value array value
     * @return  Core_Config_Contract_Group
     */
    public function set(string $key, $value) : Core_Config_Contract_Group
    {
        $this->offsetSet($key, $value);

        return $this;
    }


    /**
     * Overrides ArrayObject::offsetSet()
     * This method is called when config is changed via
     *
     * @example
     *
     *     $config->var = 'asd';
     *
     *     // OR
     *
     *     $config['var'] = 'asd';
     *
     * @param string $key The key of the config item we're changing
     * @param mixed $value The new array value
     */
    public function offsetSet($key, $value)
    {
        $this->_parent_instance->writeConfig(
          $this->_group_name,
          $key,
          $value);

        return parent::offsetSet($key, $value);
    }

}