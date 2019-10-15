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
    
    //Získání dat
    $picUrl = $_POST['to'];
    $reason = $_POST['sub'];
    
    $picUrl = mysqli_real_escape_string($connection, $picUrl);
    $reason = mysqli_real_escape_string($connection, $reason);
    
    //Získávíní ID obrázku
    $query = "SELECT id FROM obrazky WHERE zdroj='$picUrl' LIMIT 1";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        echo "alert('Nastala chyba SQL: ".mysqli_error($connection)."');";
    }
    $result = mysqli_fetch_array($result);
    $picId = $result['id'];
    
    //Odstranění hlášení
    $query = "DELETE FROM hlaseni WHERE obrazekId='$picId' AND duvod=$reason LIMIT 1";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        echo "alert('Nastala chyba SQL: ".mysqli_error($connection)."');";
    }
