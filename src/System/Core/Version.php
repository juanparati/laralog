<?php


/**
 * Version helper class
 *
 * @package     Mamuph Params Helper
 * @category    Helpers
 * @author      Mamuph Team
 * @copyright   (c) 2015-2018 Mamuph Team
 *
 */
class Core_Version
{


    /**
     * Get the current version as a human readable string
     *
     * @param array $version_info
     * @return string
     */
    public static function get(array $version_info)
    {
        return sprintf('%d.%d build: %d', $version_info['major'], $version_info['minor'], $version_info['build']);
    }



}