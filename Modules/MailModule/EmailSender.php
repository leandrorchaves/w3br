<?php

/**
 * Gerenciador de Emails.
 *
 * @author Leandro Chaves <leandro.chaves@h2asol.com>
 */
class EmailSender
{
    public $from;
    public $subject;
    public $to;
    public $body;
    public $serv;

    public function __construct()
    {
    }

    public function enviar()
    {
        try {
            ob_start();
            include_once ROOT_DIR.'/vendor/swiftmailer/swiftmailer/lib/swift_required.php';
            // Message
            $message = Swift_Message::newInstance()
                ->setFrom(EMAIL_USER)
                // ->setReplyTo(EMAIL_REPLY)
                ->setSubject($this->subject)
                ->setTo($this->to)
                ->setBcc(EMAIL_COPY)
                ->setBody($this->body)
                ->setContentType('text/html')
            ;
            // Transport
            $transport = Swift_SmtpTransport::newInstance(EMAIL_SERVER, EMAIL_PORT) //, EMAIL_SECURE
                ->setUsername(EMAIL_USER)
                ->setPassword(EMAIL_PASSWORD)
            ;
            $mailer = Swift_Mailer::newInstance($transport);
            $mailer->send($message);

//             $mail = new PHPMailer();
// //            $mail->SetLanguage("br", DIR_RAIZ . "/www/add/phpmailer/"); // Linguagem
//            $mail->IsSMTP();
//             $mail->SMTP_PORT = EMAIL_PORT; // Porta do SMTP
//         //    $mail->SMTPSecure = EMAIL_SECURE; // Tipo de comunicação segura
//            $mail->Host = EMAIL_SERVER;  // Endereço do servidor SMTP
//            $mail->SMTPAuth = true; // Requer autenticação?
//            $mail->Username = EMAIL_USER; // Usuário SMTP
//            $mail->Password = EMAIL_PASSWORD; // Senha do usuário SMTP
//             // $mail->IsMail();
//             $mail->From = EMAIL_ADDR; // E-mail do remetente
//             $mail->FromName = utf8_decode($this->from); // Nome do remetente
//
//             $mail->SetFrom(EMAIL_ADDR, TITLE);
//             $mail->Subject = utf8_decode($this->subject);
//             $mail->MsgHTML(utf8_decode($this->body)); //EmailsController::removerAcentos($this->body);
//             $mail->AddAddress($this->to); // E-mail do destinatário
//
//             //Adiciona um e-mail secundário
//             $copias = explode(",", EMAIL_COPY);
//             foreach ($copias as $copia) {
//                $mail->AddBCC($copia);
//             }
//

            // $retorno = $mail->Send();

            ob_end_clean();

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function removerAcentos($texto)
    {
        $acentos = array('Á', 'á', 'Â', 'â', 'À', 'à', 'Å', 'å', 'Ã', 'ã', 'Ä', 'ä', 'Æ', 'æ', 'É', 'é', 'Ê', 'ê', 'È',
            'è', 'Ë', 'ë', 'Ð', 'ð', );
        $trocas = array('&Aacute;', '&aacute;', '&Acirc;', '&acirc;', '&Agrave;', '&agrave;', '&Aring;', '&aring;',
            '&Atilde;', '&atilde;', '&Auml;', '&auml;', '&AElig;', '&aelig;', '&Eacute;', '&eacute;', '&Ecirc;', '&ecirc;',
            '&Egrave;', '&egrave;', '&Euml;', '&euml;', '&ETH;', '&eth;', );

        return str_replace($acentos, $trocas, $texto);
    }
}
