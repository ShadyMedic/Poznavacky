<?php
    session_start();
    
    $name = @$_POST['name_input'];
    $pass = @$_POST['pass_input'];
    
    include 'httpStats.php'; //Zahrnuje connect.php
    
    //Ochrana proti SQL injekci
    $name = mysqli_real_escape_string($connection, $name);
    $pass = mysqli_real_escape_string($connection, $pass);
    
    //Mazání předchozích chyb
    $_SESSION['loginError'] = "";
    
    //Kontrola maximální délky jména (aby nevznikaly dlouhé SQL dotazy)
    if (strlen($name) > 15)
    {
        $_SESSION['loginError'] = "Jméno nesmí být více než 15 znaků dlouhé.";
        header("Location: index.php");
        die();
    }
    
    //Kontrola maximální délky hesla (aby nevznikaly dlouhé SQL dotazy)
    if (strlen($pass) > 31)
    {
        $_SESSION['loginError'] = "Heslo nesmí být více než 31 znaků dlouhé.";
        header("Location: index.php");
        die();
    }
    
    //Hledání účtu se zadaným jménem
    $query = "SELECT id,jmeno,heslo FROM uzivatele WHERE jmeno='$name' LIMIT 1";
    $result = mysqli_query($connection, $query);
    if (empty(mysqli_num_rows($result)))    //Uživatel nenalezen
    {
        $_SESSION['loginError'] = "Uživatel s tímto jménem neexistuje.";
        header("Location: index.php");
        die();
    }
    
    //Kontrola správnosti hesla
    $result = mysqli_fetch_array($result);
    if (password_verify($pass, $result['heslo']))   //Heslo je správné
    {
        //Kontrola zvolení možnosti uchování přihlášení
        if (isset($_POST['stay_logged']))
        {
            //Vygenerovat čtrnáctimístný kód
            $code = bin2hex(random_bytes(7));   //56 bitů --> maximálně čtrnáctimístný kód

            //Uložit kód do databáze
            $userId = $result['id'];
            $query = "INSERT INTO sezeni (kod_cookie, uzivatel_id) VALUES ('".md5($code)."', $userId)";
            $innerResult = @mysqli_query($connection, $query);
            /* 
             *Poznámka: v případě, že by byl $code již někdy uložen, dotaz prostě selže a přihlášení se neuloží. Nic jiného se nestane.
             *          Jelikož je riziko, že se to stane velice malé, nebudu jej nijak ošetřovat. Kontrola, zda je již kód uložen by zbytečně zatěžovala server.
             */
            if (!$innerResult)
            {
                header("Location: errSql.html");
                die();
            }
            setcookie('instantLogin', $code, time() + 2592000, '/');    //2 592 000‬ s = 30 dní
            $_COOKIE['instantLogin'] = $code;
        }
        
        //Přihlašování
        $_SESSION['user'] = $result['id'];
        header("Location: list.php");
        die();
    }
    //Chybné heslo
    $_SESSION['loginError'] = "Špatné heslo";
    header("Location: index.php");
    die();
