<?php

/**
 * Directories list.
 *
 * @author Leandro Chaves
 * @link http://leandrochaves.com
 */
class AutoLoadList extends SingletonList{
    public static function getInstance($class = __CLASS__) {
        return parent::getInstance($class);
    }
}

?>
