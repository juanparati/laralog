<?php

interface Core_Contract_Config
{


    /**
     * Get the singleton instance of this class and enable writing at shutdown.
     *
     * @example
     *
     *     $config = Config::instance();
     *
     * @param   string $name Instance name
     * @return  Core_Contract_Config
     */
    public static function instance(string $name = 'default'
    ): Core_Contract_Config;


    /**
     * Attach a configuration reader. By default, the reader will be added as
     * the first used reader. However, if the reader should be used only when
     * all other readers fail, use `FALSE` for the second parameter.
     *
     * @example
     *
     *     $config->attach($reader);        // Try first
     *     $config->attach($reader, FALSE); // Try last
     *
     * @param   Core_Config_Contract_Source $source instance
     * @param   boolean $first add the reader as the first used object
     * @return  Core_Contract_Config
     */
    public function attach(
      Core_Config_Contract_Source $source,
      $first = true
    ): Core_Contract_Config;


    /**
     * Detach a configuration reader.
     *
     * @example
     *
     *     $config->detach($reader);
     *
     * @param   Core_Config_Contract_Source $source instance
     * @return  Core_Contract_Config
     */
    public function detach(Core_Config_Contract_Source $source
    ): Core_Contract_Config;


    /**
     * Copy one configuration group to all of the other writers.
     *
     * @example
     *
     *     $config->copy($name);
     *
     * @param   string $group configuration group name
     * @return  Core_Contract_Config
     * @throws \Exception
     */
    public function copy(string $group): Core_Contract_Config;


    /**
     * Load a configuration group. Searches all the config sources, merging all the
     * directives found into a single config group.  Any changes made to the config
     * in this group will be mirrored across all writable sources.
     *
     * @example
     *
     *     $array = $config->load($name);
     *
     * See [Mamuph_Config_Contracts_Group] for more info
     *
     * @param   string $group configuration group name
     * @return  Core_Config_Contract_Group
     * @throws  Exception
     */
    public function load($group) : Core_Config_Contract_Group;


    /**
     * Callback used by the config group to store changes made to the writer buffer
     *
     * @param string $group Group name
     * @param string $key Variable name
     * @param mixed $value The new value
     * @return Core_Contract_Config Chainable instance
     */
    public function writeConfig(string $group, string $key, $value) : Core_Contract_Config;
}