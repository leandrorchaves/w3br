<?php

/**
 * Gerenciador de Emails
 *
 * @author Leandro Chaves <leandro.chaves@h2asol.com>
 */
class EmailSender {

    public $from;
    public $subject;
    public $to;
    public $body;
    public $serv;

    function __construct() {

    }

    function enviar() {
        try {
            ob_start();

//            $headers = 'MIME-Version: 1.0' . "\r\n";
//            $headers .= 'Content-type: text/plain; charset=iso-8859-1' . "\r\n";
//            $headers .= 'From: Dia% Manutenção <diabrasil@h2asol.com.br>' . "\r\n";
//            //$headers .= 'From: '.utf8_decode($this->from).' <'.EMAIL_ADDR.'>' . "\r\n";
//            mail($this->to, $this->subject, utf8_decode($this->body), utf8_decode($headers));
//            return true;

            $mail = new PHPMailer();
//            $mail->SetLanguage("br", ROOT_DIR . "/www/add/phpmailer/"); // Linguagem
            $mail->SMTP_PORT = EMAIL_PORT; // Porta do SMTP
//            $mail->SMTPSecure = EMAIL_SECURE; // Tipo de comunicação segura
//            $mail->IsSMTP();
//            $mail->Host = EMAIL_SERVER;  // Endereço do servidor SMTP
//            $mail->SMTPAuth = true; // Requer autenticação?
//            $mail->Username = EMAIL_USER; // Usuário SMTP
//            $mail->Password = EMAIL_PASSWORD; // Senha do usuário SMTP
            $mail->IsMail();
            $mail->From = EMAIL_ADDR; // E-mail do remetente
            $mail->FromName = utf8_decode($this->from); // Nome do remetente
            $mail->AddAddress($this->to); // E-mail do destinatário
            //Adiciona um e-mail secundário
            $copias = explode(",", EMAIL_COPY);
            foreach ($copias as $copia) {
               $mail->AddBCC($copia);
            }


            $mail->IsHTML(true);
            $mail->Subject = utf8_decode($this->subject);
            $mail->Body = utf8_decode($this->body); //EmailsController::removerAcentos($this->body);
            $mail->AddCustomHeader("Content-Type: text/html; charset=ISO-8859-1\r\n");
            $retorno = $mail->Send();
            ob_end_clean();
            return $retorno;
        } catch (Exception $e) {
            return false;
        }
    }

    static function removerAcentos($texto) {
        $acentos = Array('Á', 'á', 'Â', 'â', 'À', 'à', 'Å', 'å', 'Ã', 'ã', 'Ä', 'ä', 'Æ', 'æ', 'É', 'é', 'Ê', 'ê', 'È',
            'è', 'Ë', 'ë', 'Ð', 'ð');
        $trocas = Array('&Aacute;', '&aacute;', '&Acirc;', '&acirc;', '&Agrave;', '&agrave;', '&Aring;', '&aring;',
            '&Atilde;', '&atilde;', '&Auml;', '&auml;', '&AElig;', '&aelig;', '&Eacute;', '&eacute;', '&Ecirc;', '&ecirc;',
            '&Egrave;', '&egrave;', '&Euml;', '&euml;', '&ETH;', '&eth;');
        return str_replace($acentos, $trocas, $texto);
    }

}

?>
