<?php

/**
 * Module for manage directories.
 *
 * @author Leandro Chaves
 * @link http://leandrochaves.com
 */
class DirectoriesFunctions {

    public static function path() {
        return MODULES_PATH . "/DoctrineModule";
    }

    /**
     * Scans the path for directories and if there are more than 2
     * directories i.e. "." and ".." then the directory is not empty
     * 
     * @param String $path path to verify
     */
    public static function isEmpty($path) {
        if (($files = @scandir('path_to_directory') && (count($files) > 2))) {
            return FALSE;
        }else{
            return TRUE;
        }
    }
}

?>
