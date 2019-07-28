<?php
    session_start();
    
    include 'httpStats.php'; //Zahrnuje connect.php
    include 'logger.php';
    
    $newEmail = $_GET['new'];
    
    $userdata = $_SESSION['user'];
    $username = $userdata['name'];
    $userId = $userdata['id'];
    
    //Ochrana před SQL injekcí
    $newEmail = mysqli_real_escape_string($connection, $newEmail);
    
    //Kontrola unikátnosti emailu
    $query = "SELECT email FROM uzivatele WHERE email='$newEmail' LIMIT 1";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        echo "swal('Vyskytla se chyba při práci s databází.','Pro více informací přejděte na ".$_SERVER['SERVER_NAME']."/errSql.html','error')";
        die();
    }
    if (mysqli_num_rows($result) > 0)
    {
        filelog("Uživatel $username se pokusil změnit si e-mailovou adresu na $newEmail, avšak neuspěl kvůli neunikátní nové e-mailové adrese.");
        echo "swal('Email je již používán jiným uživatelem.','','error')";
        die();
    }
    
    //Kontrola délky e-mailu
    if(strlen($newEmail) > 255)
    {
        filelog("Uživatel $username se pokusil změnit si e-mailovou adresu na $newEmail, avšak neuspěl z důvodu dlouhé nové e-mailové adresy.");
        echo "swal('Email nesmí být delší než 255 znaků.','','error')";
        die();
    }
    
    //Kontrola platného e-mailu
    if(!filter_var($newEmail, FILTER_VALIDATE_EMAIL))
    {
        filelog("Uživatel $username se pokusil změnit si e-mailovou adresu na $newEmail, avšak neuspěl z důvodu neplatného formátu nové e-mailové adresy.");
        echo "swal('E-mailová adresa nemá správný formát.','','error')";
        die();
    }
    
    //KONTROLA DAT V POŘÁDKU
    
    //Aktualizace e-mailu
    $query = "UPDATE uzivatele SET email = '$newEmail' WHERE id = $userId";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        echo "swal('Vyskytla se chyba při práci s databází.','Pro více informací přejděte na ".$_SERVER['SERVER_NAME']."/errSql.html','error')";
        die();
    }
    
    $ip = $_SERVER['REMOTE_ADDR'];
    filelog("Uživatel $username si změnil e-mail na $newEmail z IP adresy $ip.");
    
    $_SESSION['user']['email'] = $newEmail;
    echo "
        swal('E-mailová adresa byla úspěšně změněna.','','success');
        updateEmail('$newEmail');
    ";