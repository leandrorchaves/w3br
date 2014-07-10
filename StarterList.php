<?php

/**
 * Starter functions list.
 *
 * @author Leandro Chaves
 */
class StarterList extends SingletonList{
    public static function getInstance($class = __CLASS__) {
        return parent::getInstance($class);
    }
}

?>
