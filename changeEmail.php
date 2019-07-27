<?php
    session_start();
    
    include 'connect.php';
    
    $newEmail = $_GET['new'];
    
    //Ochrana před SQL injekcí
    $newEmail = mysqli_real_escape_string($connection, $newEmail);
    
    //Kontrola unikátnosti emailu
    $query = "SELECT email FROM uzivatele WHERE email='$newEmail' LIMIT 1";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        echo "swal('Vyskytla se chyba při práci s databází. Pro více informací přejděte na ".$_SERVER['SERVER_NAME']."/errSql.html','','error')";
        die();
    }
    if (mysqli_num_rows($result) > 0)
    {
        echo "swal('Email je již používán jiným uživatelem.','','error')";
        die();
    }
    
    //Kontrola délky e-mailu
    if(strlen($newEmail) > 255)
    {
        echo "swal('Email nesmí být delší než 255 znaků.','','error')";
        die();
    }
    
    //Kontrola platného e-mailu
    if(!filter_var($newEmail, FILTER_VALIDATE_EMAIL))
    {
        echo "swal('E-mailová adresa nemá správný formát.','','error')";
        die();
    }
    
    //KONTROLA DAT V POŘÁDKU
    
    $userdata = $_SESSION['user'];
    $username = $userdata['name'];
    $userId = $userdata['id'];
    
    //Aktualizace e-mailu
    $query = "UPDATE uzivatele SET email = '$newEmail' WHERE id = $userId";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        echo "swal('Vyskytla se chyba při práci s databází. Pro více informací přejděte na ".$_SERVER['SERVER_NAME']."/errSql.html','','error')";
        die();
    }
    
    $_SESSION['user']['email'] = $newEmail;
    echo "
        swal('E-mailová adresa byla úspěšně změněna.','','success');
        updateEmail('$newEmail');
    ";