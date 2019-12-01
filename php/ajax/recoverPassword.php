<?php
    session_start();
    
    include '../included/httpStats.php'; //Zahrnuje connect.php
    include '../included/logger.php';
    
    $email = $_POST['email'];
    
    //Kontrola délky e-mailu (aby nevznikaly dlouhé SQL dotazy)
    if(strlen($email) > 255)
    {
        filelog("Uživatel se pokusil zažádat o obnovu hesla, avšak neuspěl z důvodu dlouhé e-mailové adresy.");
        echo "<span>Email nesmí být delší než 255 znaků.</span>";
        die();
    }
    
    //Ochrana proti SQL injekci
    $email = mysqli_real_escape_string($connection, $email);
    
    //Kontrola platného e-mailu
    if(!filter_var($email, FILTER_VALIDATE_EMAIL))
    {
        filelog("Uživatel se pokusil zažádat o obnovu hesla, avšak neuspěl z důvodu e-mailové adresy ($email) v neplatném formátu.");
        echo "<span>E-mail nemá platný formát.</span>";
        die();
    }
    
    //E-MAIL JE OK
    
    //Kontrola existence e-mailu v databázi
    $query = "SELECT uzivatele_id FROM uzivatele WHERE email = '$email' LIMIT 1";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        echo "location.href = 'errSql.html';";
        die();
    }
    if (mysqli_num_rows($result) == 0)
    {
        filelog("Uživatel se pokusil zažádat o obnovu hesla, avšak neuspěl z důvodu neznámé e-mailové adresy ($email).");
        echo "<span>K této e-mailové adrese není přidružen žádný účet.</span>";
        die();
    }
    
    //Uživatel nalezen
    $result = mysqli_fetch_array($result);
    $userId = $result['uzivatele_id'];
    
    //Vygenerovat kód
    $done = false;
    $code = NULL;
    do
    {
        //Vygenerovat třicetidvoumístný kód pro obnovení hesla
        $code = bin2hex(random_bytes(16));   //128 bitů --> maximálně třicetidvoumístný kód
        
        //Zkontrolovat, zda již kód v databázi neexistuje
        $query = "SELECT uzivatele_id FROM obnoveni_hesel WHERE kod='$code' LIMIT 1";
        $result = mysqli_query($connection, $query);
        if (!$result)
        {
            echo "location.href = 'errSql.html';";
            die();
        }
        if (!mysqli_num_rows($result) > 0)  //Kontrola případné potřeby opakování generování kódu
        {
           $done = true;
        }
    }while ($done == false);
    
    //Smazat starý kód z databáze (pokud existuje)
    $query = "DELETE FROM obnoveni_hesel WHERE uzivatele_id=$userId";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        echo "location.href = 'errSql.html';";
        die();
    }
    
    //Uložit kód do databáze
    $query = "INSERT INTO obnoveni_hesel (kod, uzivatele_id) VALUES ('".md5($code)."', $userId)";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        echo "location.href = 'errSql.html';";
        die();
    }
    
    $ip = $_SERVER['REMOTE_ADDR'];
    filelog("Uživatel se zažádal o obnovu hesla prostřednictvím e-mailové adresy $email. z IP adresy $ip");
    
    //Poslat e-mail.
    include '../emailSender.php';
    include '../included/composeEmail.php';
    $emailResult = sendEmail($email, 'Žádost o obnovu hesla na poznavacky.chytrak.cz', getEmail(0, array("code" => $code)));
    
    if (empty($emailResult))
    {
        echo "<span style='color: #009900'>E-mail byl úspěšně odeslán</span>";
    }
    else
    {
        echo "<span>$emailResult</span>";
    }
    die();
