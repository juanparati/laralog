<?php

/**
 * Interface for config readers
 *
 * @package    Mamuph Config
 * @category   Configuration
 * @author     Mamuph Team
 * @copyright  (c) 2008-2012 Mamuph Team
 */
interface Core_Config_Contract_Reader extends Core_Config_Contract_Source {

    /**
     * Tries to load the specified configuration group
     *
     * Returns FALSE if group does not exist or an array if it does
     *
     * @param  string $group Configuration group
     * @return boolean|array
     */
    public function load(string $group);

}