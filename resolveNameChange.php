<?php
    include 'httpStats.php';     //Obsahuje session_start();
    include 'emailSender.php';
    session_start();
    
    //Kontrola, zda je uživatel administrátorem.
    $username = $_SESSION['user']['name'];
    $query = "SELECT status FROM uzivatele WHERE jmeno='$username' LIMIT 1";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        header("Location: errSql.html");
        die();
    }
    $status = mysqli_fetch_array($result)['status'];
    if ($status !== 'admin')
    {
        //Zamítnutí přístupu
        die();
    }
    
    $action = $_POST['acc'];
    $oldName = $_POST['oldName'];
    $newName = $_POST['newName'];
    
    $oldName = mysqli_real_escape_string($connection, $oldName);
    $newName = mysqli_real_escape_string($connection, $newName);
    
    if ($action === "true")
    {
        //Změna jména
        $query = "UPDATE uzivatele SET jmeno = '$newName' WHERE jmeno = '$oldName'";
        $result = mysqli_query($connection, $query);
        if (!$result)
        {
            echo "alert('Nastala chyba SQL: ".mysqli_error($connection)."');";
            die();
        }
        //Odeslat uživateli e-mail informující o změně jména
        $query = "SELECT email FROM uzivatele WHERE jmeno = '$newName'";
        $result = mysqli_query($connection, $query);
        if (!$result)
        {
            echo "alert(Nastala chyba SQL: ".mysqli_error($connection).");";
            die();
        }
        $email = mysqli_fetch_array($result)['email'];
        $emailResult = sendEmail(
            $email,
            'Vaše přihlašovací jméno bylo změněno',
            "<p>".
            "Na základě vaší žádosti na <a href='poznavacky.chytrak.cz'>poznavacky.chytrak.cz</a> bylo změněno vaše".
            "<br>".
            "uživatelské jméno na <b>$newName</b>.".
            "<br>".
            "Pod svým starým jménem (<b>$oldName</b>) se od nynějška již nebudete moci".
            "<br>".
            "přihlásit.".
            "<br>".
            "</p><p>".
            "Pokud si přejete změnit jméno zpět na staré, nebo nějaké úplně jiné,".
            "<br>".
            "můžete tak učinit odesláním další žádosti o změnu jména v nastavení".
            "<br>".
            "vašeho uživatelského účtu.".
            "<br>".
            "</p><p>".
            "Neodesílali jste žádnou žádost na změnu uživatelského jména? Je možné,".
            "<br>".
            "že někdo získal přístup k vašemu účtu. Doporučujeme vám si co".
            "<br>".
            "nejdříve změnit vaše heslo. Pokud se nemůžete přihlásit, kontaktujte".
            "<br>".
            "nás prosím na e-mailové adrese <a href='mailto:poznavacky@email.com'>poznavacky@email.com</a>".
            "</p><hr>".
            "<span style='color:#777777'>Toto je automaticky vygenerovaná zpráva. Prosíme, neodpovídejte na ni.</span>"
            );
        
        if (!empty($emailResult))
        {
            echo "alert(Automatický e-mail nemohl být odeslán. Chyba: $emailResult);";
        }
    }
    
    //Odstraňování žádosti
    $query = "DELETE FROM zadostijmena WHERE puvodni='$oldName'";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        echo "alert(Nastala chyba SQL: ".mysqli_error($connection).");";
    }
    
    //Pokud si jméno změnil sám administrátor, přepíšeme uloženou hodnotu v $_SESSION, aby se předešlo problémům s autorizací
    if ($_SESSION['user']['name'] == $oldName)
    {
        $_SESSION['user']['name'] = $newName;
    }