<?php
    session_start();
    
    include 'httpStats.php'; //Zahrnuje connect.php
    include 'logger.php';
    
    $name = urldecode(@$_POST['name']);
    $pass = urldecode(@$_POST['pass']);
    $repass = urldecode(@$_POST['rePass']);
    $email = urldecode(@$_POST['email']);
    
    $errors = array();
    
    //Kontrola minimální délky jména
    if (mb_strlen($name) < 4){array_push($errors, "Jméno musí být alespoň 4 znaky dlouhé.");}
    
    //Kontrola maximální délky jména
    if (mb_strlen($name) > 15){array_push($errors, "Jméno nesmí být více než 15 znaků dlouhé.");}
    
    //Kontrola minimální délky hesla
    if (mb_strlen($pass) < 6){array_push($errors, "Heslo musí být alespoň 6 znaků dlouhé.");}
    
    //Kontrola maximální délky hesla
    if (mb_strlen($pass) > 31){array_push($errors, "Heslo nesmí být více než 31 znaků dlouhé.");}
    
    //Ochrana proti SQL injekci (e-mail je zvlášť)
    $name = mysqli_real_escape_string($connection, $name);
    //$pass = mysqli_real_escape_string($connection, $pass);        Není potřeba escapovat, protože zadaná hodnota není použitav v SQL dotazu
    //$repass = mysqli_real_escape_string($connection, $repass);
    
    //Kontrola znaků ve jméně
    if(strlen($name) !== strspn($name, '0123456789aábcčdďeěéfghiíjklmnňoópqrřsštťuůúvwxyýzžAÁBCČDĎEĚÉFGHIÍJKLMNŇOÓPQRŘSŠTŤUŮÚVWXYZŽ ')){array_push($errors, "Jméno může obsahovat pouze písmena, číslice a mezery.");}
    
    //Kontrola volnosti jména
    $query = "SELECT id FROM uzivatele WHERE jmeno = '$name'";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        echo "location.href = 'errSql.html'";
        die();
    }
    if (mysqli_num_rows($result) > 0){array_push($errors, "Jméno je již používáno jiným uživatelem.");}
    
    //JMÉNO JE OK
    
    //Kontrola znaků v hesle
    if(strlen($pass) !== strspn($pass, '0123456789aábcčdďeěéfghiíjklmnňoópqrřsštťuůúvwxyýzžAÁBCČDĎEĚÉFGHIÍJKLMNŇOÓPQRŘSŠTŤUŮÚVWXYZŽ {}()[]#:;^,.?!|_`~@$%/+-*=\"\'')){array_push($errors, "Vaše heslo obsahuje nepovolený znak.");}
    
    //Kontrola shodnosti hesel
    if ($pass !== $repass){array_push($errors, "Hesla se neshodují.");}
    
    //HESLO JE OK
    
    if (!empty($email)) //E-mail je nepovinná položka
    {
        //Kontrola délky e-mailu
        if(mb_strlen($email) > 255){array_push($errors, "Email nesmí být delší než 255 znaků.");}
        
        //Ochrana proti SQL injekci
        $email = mysqli_real_escape_string($connection, $email);
        
        //Kontrola platného e-mailu
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){array_push($errors, "E-mail nemá platný formát.");}
        
        //Kontrola volnosti e-mailu
        $query = "SELECT id FROM uzivatele WHERE email = '$email'";
        $result = mysqli_query($connection, $query);
        if (!$result)
        {
            echo "location.href = 'errSql.html'";
            die();
        }
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
            echo "location.href = 'errSql.html'";
            die();
        }
        
        //Přihlášení
        require 'CONSTANTS.php';
        $query = "SELECT id FROM uzivatele WHERE jmeno='$name'";
        $userId = mysqli_query($connection, $query);
        if (!$userId)
        {
            echo mysqli_error($connection);
            //echo "location.href = 'errSql.html'";
            die();
        }
        $userId = mysqli_fetch_array($userId)['id'];
        
        $userData = [
            'id' => $userId,
            'name' => $name,
            'hash' => $pass,
            'email' => $email,
            'addedPics' => 0,
            'guessedPics' => 0,
            'karma' => DEFAULT_KARMA,
            'status' => DEFAULT_RANK
        ];
        $_SESSION['user'] = $userData;
        
        $ip = $_SERVER['REMOTE_ADDR'];
        fileLog("Uživatel $name se zaregistroval do systému z IP adresy $ip");
        
        //Přesměrování do systému
        echo "location.href = 'list.php'";
        die();
    }
    else    //Chybné údaje
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        fileLog("Uživatel se pokusil zaregistroval do systému z IP adresy $ip, ale zadal neplatné údaje.");
        
        foreach ($errors as $err)
        {
            echo "<li>$err</li>";
        }
        die();
    }