<?php

/**
 * Memory helper.
 *
 * @package    Mamuph Array Helper
 * @category   Helpers
 * @author     Mamuph Team
 * @copyright  (c) 2018 Mamuph Team
 */
class Core_Mem
{

    /**
     * Convert human readable memory size.
     *
     * It supports different unit names like:
     * - byte, bytes, b
     * - kilobyte, kilobytes, KB, mb, k
     * - megabyte, megabytes, MB, mb, m
     * ...
     *
     * @example
     *
     *     // Convert
     *     $kilobytes = Mem::convert(1024, 'kilobytes') // Result 1 KB
     *     $megabytes = Mem::convert('1024Kb', 'MB')    // Result 1 MB
     *     $bytes     = Mem::convert('1MB')             // Result 1048576 bytes
     *     $terabytes = Mem::convert('1024 Megabytes', 'T') // Result 0.00098 TB
     *
     * @param  mixed  $strval
     * @param  string $to_unit (Default: bytes)
     * @return mixed
     */
    public static function convert($strval, string $to_unit = 'b')
    {
        $strval    = strtolower(str_replace(' ', '', $strval));
        $val       = floatval($strval);
        $to_unit   = strtolower(trim($to_unit))[0];
        $from_unit = str_replace($val, '', $strval);
        $from_unit = empty($from_unit) ? 'b' : trim($from_unit)[0];
        $units     = 'kmgtph';  // (k)ilobyte, (m)egabyte, (g)igabyte and so on...


        // Convert to bytes
        if ($from_unit !== 'b')
            $val *= 1024 ** (strpos($units, $from_unit) + 1);


        // Convert to unit
        if ($to_unit !== 'b')
            $val /= 1024 ** (strpos($units, $to_unit) + 1);


        return $val;
    }

}