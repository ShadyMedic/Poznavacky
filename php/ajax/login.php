<?php
    session_start();
    
    $name = urldecode(@$_POST['name']);
    $pass = urldecode(@$_POST['pass']);
    
    include '../included/httpStats.php'; //Zahrnuje connect.php
    include '../included/logger.php';
    
    //Kontrola maximální délky jména (aby nevznikaly dlouhé SQL dotazy) - je potřeba provést před mysqli_real_escape_string
    if (mb_strlen($name) > 15)
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        fileLog("Uživatel se pokusil přihlásit s příliš dlouhým jménem z IP adresy $ip");
        echo "<span>Jméno nesmí být více než 15 znaků dlouhé.</span>";
        die();
    }
    
    //Kontrola maximální délky hesla (aby nevznikaly dlouhé SQL dotazy) - je potřeba provést před mysqli_real_escape_string
    if (mb_strlen($pass) > 31)
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        fileLog("Uživatel se pokusil přihlásit s příliš dlouhým heslem z IP adresy $ip");
        
        echo "<span>Heslo nesmí být více než 31 znaků dlouhé.</span>";
        die();
    }
    
    //Ochrana proti SQL injekci
    $name = mysqli_real_escape_string($connection, $name);
    //$pass = mysqli_real_escape_string($connection, $pass);    Nemusí být escapováno - hodnota není použita v SQL dotazu nezahešovaná
    
    //Hledání účtu se zadaným jménem
    $query = "SELECT id,jmeno,heslo,email,pridaneObrazky,uhodnuteObrazky,karma,status FROM uzivatele WHERE jmeno='$name' LIMIT 1";
    $result = mysqli_query($connection, $query);
    if (empty(mysqli_num_rows($result)))    //Uživatel nenalezen
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        fileLog("Uživatel se pokusil přihlásit k neexistujícímu účtu ($name) z IP adresy $ip");
        
        echo "<span>Uživatel s tímto jménem neexistuje.</span>";
        die();
    }
    
    //Kontrola správnosti hesla
    $result = mysqli_fetch_array($result);
    if (password_verify($pass, $result['heslo']))   //Heslo je správné
    {
        //Kontrola zvolení možnosti uchování přihlášení
        if ($_POST['rePass'] === 'true')
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
                echo "location.href = 'errSql.html';";
                die();
            }
            setcookie('instantLogin', $code, time() + 2592000, '/');    //2 592 000‬ s = 30 dní
            $_COOKIE['instantLogin'] = $code;
        }
        
        //Přihlašování
        $userData = [
            'id' => $result['id'],
            'name' => $result['jmeno'],
            'hash' => $result['heslo'],
            'email' => $result['email'],
            'addedPics' => $result['pridaneObrazky'],
            'guessedPics' => $result['uhodnuteObrazky'],
            'karma' => $result['karma'],
            'status' => $result['status']
        ];
        $_SESSION['user'] = $userData;
        
        //Aktualizace času posledního přihlášení
        $userId = $userData['id'];
        $query = "UPDATE uzivatele SET posledniPrihlaseni='".date('Y-m-d H:i:s')."' WHERE id=$userId";
        $result = mysqli_query($connection, $query);
        
        $ip = $_SERVER['REMOTE_ADDR'];
        $username = $_SESSION['user']['name'];
        fileLog("Uživatel $username se přihlásil z IP adresy $ip");
        
        echo "location.href = 'list.php';";  //Přesměrování do systému
        die();
    }
    //Chybné heslo
    $ip = $_SERVER['REMOTE_ADDR'];
    fileLog("Uživatel $name se pokusil přihlásit se špatným heslem z IP adresy $ip");
    
    echo "<span>Špatné heslo</span>";
    
