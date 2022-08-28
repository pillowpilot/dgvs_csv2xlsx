<?php

/*
 * This namespace separates utilities functions from the main logic.
 */

namespace utils;

require_once('pclzip.lib.php');

class Utils
{
    /*
     * Compute the string corresponding to the column index (0-based).
     * Originally from: https://github.com/PHPOffice/PHPExcel/blob/1.8/Classes/PHPExcel/Cell.php#L833
     */
    public static function stringFromColumnIndex($pColumnIndex = 0)
    {
        //    Using a lookup cache adds a slight memory overhead, but boosts speed
        //    caching using a static within the method is faster than a class static,
        //        though it's additional memory overhead
        static $_indexCache = array();

        if (!isset($_indexCache[$pColumnIndex])) {
            // Determine column string
            if ($pColumnIndex < 26) {
                $_indexCache[$pColumnIndex] = chr(65 + $pColumnIndex);
            } elseif ($pColumnIndex < 702) {
                $_indexCache[$pColumnIndex] = chr(64 + ($pColumnIndex / 26)) .
                                                chr(65 + $pColumnIndex % 26);
            } else {
                $_indexCache[$pColumnIndex] = chr(64 + (($pColumnIndex - 26) / 676)) .
                                                chr(65 + ((($pColumnIndex - 26) % 676) / 26)) .
                                                chr(65 + $pColumnIndex % 26);
            }
        }
        return $_indexCache[$pColumnIndex];
    }

    /*
     * Recursively copy a directory.
     * Originally from: bilgisayarcilik/Remote-File-Manager
     */
    public static function copy_directory($directory, $destination)
    {
        $destination = $destination . basename($directory);
        # The directory will be created
        if (!file_exists($destination)) {
            if (!mkdir($destination)) {
                return false;
            }
        }
        $directory_list = @scandir($directory);
        # Directory scanning
        if (!$directory_list) {
            return false;
        }
        foreach ($directory_list as $item_name) {
            $item = $directory . DIRECTORY_SEPARATOR . $item_name;
            if ($item_name == '.' || $item_name == '..') {
                continue;
            }
            if (filetype($item) == 'dir') {
                Utils::copy_directory($item, $destination . DIRECTORY_SEPARATOR);
            } else {
                // var_dump($item);
                // var_dump($destination . DIRECTORY_SEPARATOR . $item_name);
                if (!copy($item, $destination . DIRECTORY_SEPARATOR . $item_name)) {
                    return false;
                }
            }
        }
        return true;
    }

    /*
     * Compress a directory into a zip file. Using the pclzip library.
     */
    public static function zip_directory($directory, $zip_destination)
    {
        echo "Creating zip archive {$zip_destination}... ";
        $zip = new \PclZip($zip_destination);
        if ($zip->create($directory, PCLZIP_OPT_REMOVE_PATH, $directory) == 0)
            echo $zip->errorInfo(true);
        echo "done.\n"; 
    }
}

?>