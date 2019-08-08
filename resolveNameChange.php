<?php
    include 'httpStats.php';     //Obsahuje session_start();
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
            echo "alert(Nastala chyba SQL: ".mysqli_error($connection).");";
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