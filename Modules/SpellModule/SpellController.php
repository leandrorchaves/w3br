<?php

/**
 * Proxy para consulta do corretor ortográfico do google.
 *
 * @author Leandro Chaves
 */
class SpellController {

    public function checkAction() {
        $response = new Response();
        try {
            $url = "http://www.google.com/tbproxy/spell?lang=pt";
            $text = urldecode(isset($_POST['text']) ? $_POST['text'] : $_GET['text']);

            $body = '<?xml version="1.0" encoding="utf-8" ?>';
            $body .= '<spellrequest textalreadyclipped="0" ignoredups="1" ignoredigits="1" ignoreallcaps="1">';
            $body .= '<text>' . $text . '</text>';
            $body .= '</spellrequest>';
            $contents = $this->postData($url, $body);
            $xml = simplexml_load_string($contents);

            // faz o parse do XML
            $dados = Array();
            foreach ($xml->c as $c) {
                $linha = Array();
                $linha['values'] = explode("\t",strip_tags($c->asXML()));
               foreach ($c->attributes() as $k=>$a) {
                    $linha[$k] = (String)$a; //strip_tags(
                }
                $dados[] = $linha;
            }
            $response->sucesso = 1;
            $response->dados = $dados;
        } catch (Exception $e) {
            $response->sucesso = 1;
            $response->dados = Array();
//            $response->sucesso = 0;
//            $response->erro = $e->getMessage();
//            $response->trace = $e->getTraceAsString();
        }
        echo json_encode($response);
    }

    private function postData($url, $data, $optional_headers = null) {
        $params = array('http' => array
                (
                'method' => 'POST',
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'content' => $data//http_build_query($data, "", "&")
                ));
        if ($optional_headers !== null) {
            $params['http']['header'] = $optional_headers;
        }
        $ctx = stream_context_create($params);
        $fp = @fopen($url, 'rb', false, $ctx);
        if (!$fp) {
            throw new Exception("Problem with $url");
        }
        $response = @stream_get_contents($fp);
        if ($response === false):
            throw new Exception("Problem reading data from $url");
        endif;
        return $response;
    }

    function pfopen($url) {
        $proxy_server = "leandro.chaves:sucesso1@10.1.1.7";
        $proxy_port = 8000;

        if (substr($url, 0, 7) <> 'http://') {
            return false;
        }

        $proxycon = fsockopen($proxy_server, $proxy_port); //, $errno, $errstr
        fputs($proxycon, "GET " . $url . " HTTP/1.0 \r\n\r\n");
        return $proxycon;
//        $reading_headers = true;
//        while (!feof($proxycon)) {
//            $curline = fgets($proxycon, 4096);
//
//            if ($curline == "\r\n") {
//                $reading_headers = false;
//            }
//            if (!$reading_headers) {
//                $filecontent .= $curline;
//            }
//        }
//
//        fclose($proxycon);
//        return $filecontent;
    }

}