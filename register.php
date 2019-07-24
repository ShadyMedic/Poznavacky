<?php
    session_start();

    $name = @$_POST['name_input'];
    $pass = @$_POST['pass_input'];
    $repass = @$_POST['repass_input'];
    $email = @$_POST['email_input'];
    
    include 'httpStats.php'; //Zahrnuje connect.php
    
    //Ochrana proti SQL injekci
    $name = mysqli_real_escape_string($connection, $name);
    $pass = mysqli_real_escape_string($connection, $pass);
    $repass = mysqli_real_escape_string($connection, $repass);
    $email = mysqli_real_escape_string($connection, $email);
    
    //Mazání předchozích chyb
    $_SESSION['registerErrors'] = array();
    
    $errors = array();
    
    //Kontrola minimální délky jména
    if (strlen($name) < 4){array_push($errors, "Jméno musí být alespoň 4 znaky dlouhé.");}
    
    //Kontrola maximální délky jména
    if (strlen($name) > 15){array_push($errors, "Jméno nesmí být více než 15 znaků dlouhé.");}
    
    //Kontrola znaků ve jméně
    if(strlen($name) !== strspn($name, '0123456789aábcčdďeěéfghiíjklmnňoópqrřsštťuůúvwxyýzžAÁBCČDĎEĚÉFGHIÍJKLMNŇOÓPQRŘSŠTŤUŮÚVWXYZŽ')){array_push($errors, "Jméno může obsahovat pouze písmena a číslice.");}
    
    //Kontrola volnosti jména
    $query = "SELECT id FROM uzivatele WHERE jmeno = '$name'";
    $result = mysqli_query($connection, $query);
    if (mysqli_num_rows($result) > 0){array_push($errors, "Jméno je již používáno jiným uživatelem.");}
    
    //JMÉNO JE OK
    
    //Kontrola minimální délky hesla
    if (strlen($pass) < 6){array_push($errors, "Heslo musí být alespoň 6 znaků dlouhé.");}
    
    //Kontrola maximální délky hesla
    if (strlen($pass) > 31){array_push($errors, "Heslo nesmí být více než 31 znaků dlouhé.");}
    
    //Kontrola znaků v hesle
    if(strlen($pass) !== strspn($pass, '0123456789aábcčdďeěéfghiíjklmnňoópqrřsštťuůúvwxyýzžAÁBCČDĎEĚÉFGHIÍJKLMNŇOÓPQRŘSŠTŤUŮÚVWXYZŽ{}()[]#:;^,.?!|&_`~@$%/\\+-*=\"\'')){array_push($errors, "Vaše heslo obsahuje nepovolený znak.");}
    
    //Kontrola shodnosti hesel
    if ($pass !== $repass){array_push($errors, "Hesla se neshodují.");}
    
    //HESLO JE OK
    
    if (!empty($email)) //E-mail je nepovinná položka
    {
        //Kontrola délky e-mailu
        if(strlen($email) > 255){array_push($errors, "Email nesmí být delší než 255 znaků.");}
        
        //Kontrola platného e-mailu
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){array_push($errors, "E-mail nemá platný formát.");}
        
        //Kontrola volnosti e-mailu
        $query = "SELECT id FROM uzivatele WHERE email = '$email'";
        $result = mysqli_query($connection, $query);
        if (mysqli_num_rows($result) > 0){array_push($errors, "E-mail je již používán jiným uživatelem.");}
    }
    
    //E-MAIL JE OK, NEBO NENÍ VYPLNĚN
    
    if (count($errors) == 0)    //Žádné chyby
    {
        //Ukládání dat do databáze
        $pass = password_hash($pass, PASSWORD_DEFAULT);
        $query = "INSERT INTO uzivatele (jmeno, heslo, email, posledniPrihlaseni) VALUES ('$name', '$pass', '$email', '".date('Y-m-d H:i:s')."')";
        $result = mysqli_query($connection, $query);
        if (!$result)
        {
            header("Location: errSql.html");
            //echo "SQL errors";
            die();
        }
        
        //Přihlášení
        $query = "SELECT id FROM uzivatele WHERE name='$name'";
        $userId = mysqli_query($connection, $query);
        $userId = mysqli_fetch_array($userId)['id'];
        $_SESSION['user'] = $userId;
        
        //Přesměrování do systému
        header("Location: list.php");
        //echo "No errors";
        die();
    }
    else    //Chybné údaje
    {
        $_SESSION['registerErrors'] = implode(';',$errors);
        header("Location: index.php");
        //echo "Errors";
        die();
    }