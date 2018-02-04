<?php

interface Core_Config_Contract_Group
{

    /**
     * Return the current group in serialized form.
     *
     * @example
     *
     *     echo $config;
     *
     * @return  string
     */
    public function __toString() : string;


    /**
     * Alias for getArrayCopy()
     *
     * @return array Array copy of the group's config
     */
    public function asArray() : array;


    /**
     * Returns the config group's name
     *
     * @return string The group name
     */
    public function groupName() : string;


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
    public function get(string $key, string $default = null);


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
    public function set(string $key, $value) : Core_Config_Contract_Group;


}