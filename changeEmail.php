<?php
    session_start();
    
    include 'httpStats.php'; //Zahrnuje connect.php
    include 'logger.php';
    
    $newEmail = urldecode($_POST['newEmail']);
    $password = $_POST['oldPass'];
    
    $userdata = $_SESSION['user'];
    $username = $userdata['name'];
    $userId = $userdata['id'];
    
    //Hledání účtu se zadaným jménem
    $query = "SELECT id,heslo FROM uzivatele WHERE jmeno='$username' LIMIT 1";
    $result = mysqli_query($connection, $query);
    if (empty(mysqli_num_rows($result)))    //Uživatel nenalezen
    {
        fileLog("Uživatel se pokusil odstranit neexistující účet ($user) z IP adresy $ip");
        $ip = $_SERVER['REMOTE_ADDR'];
        
        echo "swal('Něco se pokazilo.','Zkuste to prosím později, nebo se zkuste odhlásit a znovu přihlásit.','error')";
        filelog("Uživatel $username se pokusil změnit si e-mailovou adresu z IP adresy $ip, ale neuspěl kvůli neplatnému jménu.");
        die();
    }
    
    //Kontrola správnosti hesla
    $result = mysqli_fetch_array($result);
    if (!password_verify($password, $result['heslo']))  //Heslo je špatně
    {
        die();
    }
    
    //Kontrola délky e-mailu
    if(mb_strlen($newEmail) > 255)
    {
        filelog("Uživatel $username se pokusil změnit si e-mailovou adresu, avšak neuspěl z důvodu dlouhé nové e-mailové adresy.");
        echo "swal('Email nesmí být delší než 255 znaků.','','error')";
        die();
    }
    
    //Ochrana před SQL injekcí
    $newEmail = mysqli_real_escape_string($connection, $newEmail);
    
    //Kontrola unikátnosti emailu (pokud se ho uživatel nepokouší odebrat)
    if (!empty($newEmail))
    {
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
    }
    
    //Kontrola platného e-mailu (pokud se ho uživatel nepokouší odebrat)
    if(!(filter_var($newEmail, FILTER_VALIDATE_EMAIL) || empty($newEmail)))
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
    
    if (empty($newEmail))
    {
        filelog("Uživatel $username odebral svou e-mailovou adresu z IP adresy $ip.");
        $_SESSION['user']['email'] = $newEmail;
        echo "
            swal('E-mailová adresa byla úspěšně odebrána.','','success');
            updateEmail('$newEmail');
        ";
    }
    else
    {
        filelog("Uživatel $username si změnil e-mail na $newEmail z IP adresy $ip.");
    
        $_SESSION['user']['email'] = $newEmail;
        echo "
            swal('E-mailová adresa byla úspěšně změněna.','','success');
            updateEmail('$newEmail');
            ";
    }