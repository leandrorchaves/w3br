<?php
/**
 * Funções úteis
 *
 * @author Leandro Chaves http://leandrochaves.com
 */
class W3brUtils {
    public static function getValue($array,$field,$return=false){
        return isset($array[$field])?$array[$field]:$return;
    }
}
?>
