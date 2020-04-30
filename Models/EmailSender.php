<?php
use PHPMailer\PHPMailer;
use PHPMailer\Exception;

/** 
 * Třída sloužící k odesílání e-mailových zpráv
 * @author Jan Štěch
 */
class EmailSender
{
    private const EMAIL_CHARSET = 'UTF-8';
    private const EMAIL_ENCODING = 'base64';
    private const USE_SMTP = true;
    private const USE_SMTP_Auth = true;
    private const SMTP_SECURE = 'tls';
    private const SMTP_HOST = 'smtp.gmail.com';
    private const SMTP_PORT = '587';
    private const EMAIL_USERNAME = 'webexamlist@gmail.com';
    private const EMAIL_PASSWORD = 'SECRET';
    
    /**
     */
    public function sendMail($to, $subject, $message, $fromAddress = 'poznavacky@email.com', $fromName = 'Poznávačky', $isHTML = true)
    {
        $mail = $this->setMail($to, $subject, $message, $fromAddress, $fromName, $isHTML);
        return $this->sendPreparedEmail($mail);
    }
    
    public function setMail($to, $subject, $message, $fromAddress = 'poznavacky@email.com', $fromName = 'Poznávačky', $isHTML = true)
    {
        $mail = new PHPMailer();
        
        $mail->CharSet = self::EMAIL_CHARSET;
        $mail->Encoding = self::EMAIL_ENCODING;
        
        if (self::USE_SMTP){$mail->isSMTP();}
        $mail->SMTPAuth = self::USE_SMTP_Auth;
        $mail->SMTPSecure = self::SMTP_SECURE;
        $mail->Host = self::SMTP_HOST;
        $mail->Port = self::SMTP_PORT;
        $mail->Username = self::EMAIL_USERNAME;
        $mail->Password = self::EMAIL_PASSWORD;
        
        if ($isHTML){$mail->isHTML();}
        $mail->SetFrom($fromAddress, $fromName, true);
        $mail->addReplyTo($fromAddress, $fromName);
        $mail->Subject = $subject;
        $mail->Body = $message;
        $mail->AddAddress($to);
        
        return $mail;
    }
    
    public function sendPreparedEmail($mail)
    {
        $result = $mail->Send();
        if(!$result)
        {
            throw new Exception('E-mail nemohl být odeslán. Zkuste to prosím znovu později');
        }

        return true;
    }
}

