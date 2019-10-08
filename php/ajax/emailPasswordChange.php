<?php
    session_start();
    
    include '../included/httpStats.php'; //Zahrnuje connect.php
    include '../included/logger.php';
    
    $newPass = urldecode($_POST['new']);
    $rePass = urldecode($_POST['reNew']);
    $token = $_POST['token'];
    
    //Ochrana před SQL injekcí
    /*  Není potřeba escapovat, protože hodnota je před použitím v SQL dotazu zahešována.
     $newPass = mysqli_real_escape_string($connection, $newPass);
     $rePass = mysqli_real_escape_string($connection, $rePass);
     */
    $token = mysqli_real_escape_string($connection, $token);
    
    //Kontrola správnosti kódu (už sice bylo zkontrolováno v emailPasswordRecovery.php, ale pro případ modifikace cookies to raději zkontrolujeme znovu)
    $query = "SELECT uzivatel_id FROM obnovenihesel WHERE kod='".md5($token)."' LIMIT 1";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        echo "swal('Vyskytla se chyba při práci s databází.','Pro více informací přejděte na ".$_SERVER['SERVER_NAME']."/errSql.html','error')";
        die();
    }
    if (mysqli_num_rows($result) < 1)
    {
        echo "swal('Váš odkaz není platný', 'Buďto již vypršela jeho platnost, byl již použit, nebo obsahuje špatný kód obnovy hesla.','error')";
        die();
    }
    $userId = mysqli_fetch_array($result);
    $userId = $userId['uzivatel_id'];
    
    //Kontrola délky nového hesla
    if (strlen($newPass) < 6)
    {
        filelog("Uživatel s ID $userId se pokusil změnit si heslo pomocí odkazu z e-mailu, avšak neuspěl kvůli krátkému novému heslu.");
        echo "swal('Nové heslo musí být alespoň 6 znaků dlouhé.','','error')";
        die();
    }
    if (strlen($newPass) > 31)
    {
        filelog("Uživatel s ID $userId se pokusil změnit si heslo pomocí odkazu z e-mailu, avšak neuspěl kvůli dlouhému novému heslu.");
        echo "swal('Nové heslo nesmí být více než 31 znaků dlouhé.','','error')";
        die();
    }
    
    //Kontrola znaků v hesle
    if (strlen($newPass) !== strspn($newPass, '0123456789aábcčdďeěéfghiíjklmnňoópqrřsštťuůúvwxyýzžAÁBCČDĎEĚÉFGHIÍJKLMNŇOÓPQRŘSŠTŤUŮÚVWXYZŽ {}()[]#:;^,.?!|_`~@$%/+-*=\"\''))
    {
        filelog("Uživatel s ID $userId se pokusil změnit si heslo pomocí odkazu z e-mailu, avšak neuspěl kvůli přítomnosti nepovolených znaků v novém hesle.");
        echo "swal('Nové heslo obsahuje nepovolený znak.','','error')";
        die();
    }
    
    //Kontrola shodnosti hesel
    if ($newPass !== $rePass)
    {
        filelog("Uživatel s ID $userId se pokusil změnit si heslo pomocí odkazu z e-mailu, avšak neuspěl kvůli neshodným novým heslům.");
        echo "swal('Nová hesla se neshodují.','','error')";
        die();
    }
    
    //KONTROLA DAT V POŘÁDKU
    
    //Aktualizace hesla
    $newPass = password_hash($newPass, PASSWORD_DEFAULT);
    $query = "UPDATE uzivatele SET heslo = '$newPass' WHERE id = $userId";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        echo "swal('Vyskytla se chyba při práci s databází.','Pro více informací přejděte na ".$_SERVER['SERVER_NAME']."/errSql.html','error')";
        die();
    }
    
    $ip = $_SERVER['REMOTE_ADDR'];
    filelog("Uživatel s ID $userId si změnil heslo z IP adresy $ip pomocí odkazu z e-mailu.");
    
    echo "swal('Heslo bylo úspěšně změněno.','','success').then(function() {window.location = 'index.php';})";
    
    //Odstraňování kódu z databáze.
    $query = "DELETE FROM obnovenihesel WHERE kod='".md5($token)."'";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        echo "swal('Vyskytla se chyba při práci s databází.','Pro více informací přejděte na ".$_SERVER['SERVER_NAME']."/errSql.html','error')";
        die();
    }
    /*
    //Přesměrování zpět na index stránku
    echo "location.href = 'index.php';";
    */
