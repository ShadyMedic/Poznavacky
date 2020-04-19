<?php
    include '../included/httpStats.php';
    include '../emailSender.php';
    include '../included/composeEmail.php';
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
    
    $oldName = mysqli_real_escape_string($connection, $oldName);
    
    if ($action === "true")
    {
        $newName = $_POST['newName'];
        $newName = mysqli_real_escape_string($connection, $newName);
        
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
        $emailResult = sendEmail($email, 'Vaše přihlašovací jméno bylo změněno', getEmail(1, array("oldName" => $oldName, "newName" => $newName)));
        
        if (!empty($emailResult))
        {
            echo "alert(Automatický e-mail nemohl být odeslán. Chyba: $emailResult);";
        }
        
        //Pokud si jméno změnil sám administrátor, přepíšeme uloženou hodnotu v $_SESSION, aby se předešlo problémům s autorizací
        if ($_SESSION['user']['name'] == $oldName)
        {
            $_SESSION['user']['name'] = $newName;
        }
    }
    else
    {
        $reason = $_POST['msg'];
        
        //Odeslat uživateli e-mail informující o zamítnutí žádosti.
        $query = "SELECT email FROM uzivatele WHERE jmeno = '$oldName'";
        $result = mysqli_query($connection, $query);
        if (!$result)
        {
            echo "alert(Nastala chyba SQL: ".mysqli_error($connection).");";
            die();
        }
        $email = mysqli_fetch_array($result)['email'];
        $emailResult = sendEmail($email, 'Vaše žádost o změnu jména byla zamítnuta', getEmail(2, array("oldName" => $oldName, "reason" => $reason)));
        
        if (!empty($emailResult))
        {
            echo "alert(Automatický e-mail nemohl být odeslán. Chyba: $emailResult);";
        }
    }
    
    //Odstraňování žádosti - klíč "uzivatele_jmeno" v tabulce "zadosti_jmena" se při aktualizaci sloupce "jmeno" v tabulce "uzivatele" změní na NULL, a tak se zde odstraňují všechny žádosti "bez autora"
    $query = "DELETE FROM zadosti_jmena WHERE uzivatele_jmeno IS NULL";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        echo "alert(Nastala chyba SQL: ".mysqli_error($connection).");";
    }
