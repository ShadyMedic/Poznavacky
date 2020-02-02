<?php
    session_start();
    
    include '../included/httpStats.php'; //Zahrnuje connect.php
    include '../included/logger.php';
    require '../emailSender.php'; //Zahrnuje CONSTANTS.php
    
    $email = $_POST['email'];
    $name = $_POST['name'];
    $code = $_POST['code'];
    $info = nl2br($_POST['info']);
    
    if (empty($email))
    {
        $email = $_SESSION['user']['email'];
    }
    $username = $_SESSION['user']['name'];
    
    $subject = "$username zažádal o založení nové třídy";
    $message = "";
    $message .= "<span>Uživatel $username zažádal o zaožení nové třídy na ".$_SERVER['SERVER_NAME']."</span><br>";
    $message .= "<h3>Poskytnuté údaje:</h3>";
    $message .= "<span><b>Jméno třídy:</b> $name</span><br>";
    $message .= "<span><b>Kód třídy:</b> $code</span><br>";
    $message .= "<span><b>Další informace:</b></span><br>";
    $message .= "<fieldset style='width: fit-content; height: fit-content;'>$info</fieldset><br>";
    $message .= "<br><span>Email pro odpověď: <a href='mailto:$email'>$email</a></span>";
    
    sendEmail(ADMIN_EMAIL, $subject, $message, $email, $username);
    fileLog("Uživatel $username zažádal o vytvoření nové třídy jménem $name.");