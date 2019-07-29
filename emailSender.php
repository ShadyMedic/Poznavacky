<?php
/*  
 * Nová adresa
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    function sendEmail($to, $subject, $message, $fromAddress = 'poznavacky@email.com', $fromName = 'Poznávačky')
    {
        require '../phpMailer/src/Exception.php';
        require '../phpMailer/src/PHPMailer.php';
        require '../phpMailer/src/SMTP.php';
        
        $mail = new PHPMailer();
        
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        
        $mail->isSMTP();
        $mail->SMTPAuth = true;                         //TBG
        $mail->SMTPSecure = 'tls';                      //TBG
        $mail->Host = 'smtp.gmail.com';                 //TBG
        $mail->Port = '587';                            //TBG
        $mail->isHTML();
        $mail->Username = 'poznavacky@email.com';
        $mail->Password = 'SECRET';           //TBG
        $mail->SetFrom($fromAddress, $fromName, true);
        $mail->Subject = $subject;
        $mail->Body = $message;
        $mail->AddAddress($to);
        
        $result = $mail->Send();
        if(!$result){echo "<script>alert('The e-mail was not send!\n".$mail->ErrorInfo."');</script>";}
    }
*/

/*Stará adresa*/
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
function sendEmail($to, $subject, $message, $fromAddress = 'poznavacky@email.com', $fromName = 'Poznávačky')
{
    require 'phpMailer/src/Exception.php';
    require 'phpMailer/src/PHPMailer.php';
    require 'phpMailer/src/SMTP.php';
    
    $mail = new PHPMailer();
    
    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';
    
    $mail->isSMTP();
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = 'tls';
    $mail->Host = 'smtp.gmail.com';
    $mail->Port = '587';
    $mail->isHTML();
    $mail->Username = 'webexamlist@gmail.com';
    $mail->Password = 'SECRET';
    $mail->SetFrom($fromAddress, $fromName, true);
    $mail->Subject = $subject;
    $mail->Body = $message;
    $mail->AddAddress($to);
    
    $result = $mail->Send();
    if(!$result)
    {
        return "E-mail nemohl být odeslán! Chyba: ".$mail->ErrorInfo." Prosíme, kontaktujte správce.";
    }
    else
    {
        //return "E-mail byl úspěšně odeslán.";
        return NULL;
    }
}
?>