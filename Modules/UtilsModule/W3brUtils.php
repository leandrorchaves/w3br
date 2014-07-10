<?php

/**
 * Funções úteis
 *
 * @author Leandro Chaves
 */
class W3brUtils {

    /**
     *
     * @param Array $array Array de onde se quer obter o valor
     * @param String $field Posição que se que obtero valor
     * @return Object O valor do Array na posição informada ou false se não existir
     */
    public static function getValue($array, $field) {
        if (is_array($array) && isset($array[$field])) {
            return $array[$field];
        } else if (is_object($array) && isset($array->$field)) {
            return $array->$field;
        } else {
            return false;
        }
    }

    /**
     *
     * @param Array $array Array de onde se quer obter o valor
     * @param String $field Posição que se que obtero valor
     * @return Object O valor do Array na posição informada ou false se não existir
     */
    public static function getJson($array, $field) {
        $x = self::getValue($array, $field);
        if ($x) {
            return get_magic_quotes_gpc() ? json_decode(stripcslashes($x)) : json_decode($x);
        } else {
            return false;
        }
    }

    /**
     * Converte uma string para uppercase (incusive acentos).
     * @param String $string
     * @return String
     */
    public static function fullUpper($string) {
        return strtr(strtoupper($string), array(
            "à" => "À",
            "è" => "È",
            "ì" => "Ì",
            "ò" => "Ò",
            "ù" => "Ù",
            "á" => "Á",
            "é" => "É",
            "í" => "Í",
            "ó" => "Ó",
            "ú" => "Ú",
            "â" => "Â",
            "ê" => "Ê",
            "î" => "Î",
            "ô" => "Ô",
            "û" => "Û",
            "ç" => "Ç",
            "ã" => "Ã"
        ));
    }

}

?>
