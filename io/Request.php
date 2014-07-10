<?php

/**
 * Objeto padrão de resposta ao navegador.
 *
 * @author Leandro Chaves
 */
class Request {
    /**
     * Converte um json recebido via post em objeto.
     * @param String $index
     * @return Object
     */
    public static function getJsonPost($index) {
        $response = json_decode(stripcslashes($_POST[$index]));
        if (!$response) {
            $response = json_decode($_POST[$index]);
        }
        return $response;
    }
    /**
     * retorna um valor recebido via post.
     * @param String $index
     * @return Object 
     */
    public static function getPost($index) {
        return $_POST[$index];
    }
}

?>