<?php
    include '../included/httpStats.php';
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

    $username = $_POST['oldName'];
    $addedPics = $_POST['aPics'];
    $guessedPics = $_POST['gPics'];
    $karma = $_POST['karma'];
    $status = $_POST['status'];
    
    $username = mysqli_real_escape_string($connection, $username);
    $addedPics = mysqli_real_escape_string($connection, $addedPics);
    $guessedPics = mysqli_real_escape_string($connection, $guessedPics);
    $karma = mysqli_real_escape_string($connection, $karma);
    $status = mysqli_real_escape_string($connection, $status);
    
    //Ovlivnění databáze
    $query = "UPDATE uzivatele SET pridane_obrazky = $addedPics, uhodnute_obrazky = $guessedPics, karma = $karma, status = '$status' WHERE jmeno  = '$username'";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        echo "alert('Nastala chyba SQL: ".mysqli_error($connection)."');";
    }
