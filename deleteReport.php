<?php
    include 'httpStats.php';
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
    
    //Získání dat
    $pId = $_POST['oldName'];
    $picUrl = $_POST['to'];
    $reason = $_POST['sub'];
    
    $picUrl = mysqli_real_escape_string($connection, $picUrl);
    $pId = mysqli_real_escape_string($connection, $pId);
    $reason = mysqli_real_escape_string($connection, $reason);
    
    //Získávíní ID obrázku
    $query = "SELECT id FROM ".$pId."obrazky WHERE zdroj='$picUrl' LIMIT 1";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        echo "alert('Nastala chyba SQL: ".mysqli_error($connection)."');";
    }
    $result = mysqli_fetch_array($result);
    $picId = $result['id'];
    
    //Odstranění hlášení
    $tableName = $pId.'hlaseni';
    $query = "DELETE FROM $tableName WHERE obrazekId='$picId' AND duvod=$reason LIMIT 1";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        echo "alert('Nastala chyba SQL: ".mysqli_error($connection)."');";
    }