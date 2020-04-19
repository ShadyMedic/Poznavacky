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
    $picId = $_POST['sub'];
    
    $picId = mysqli_real_escape_string($connection, $picId);
    
    //Odstavení obrázku
    $query = "UPDATE obrazky SET povoleno = 0 WHERE obrazky_id=$picId LIMIT 1";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        echo "alert('Nastala chyba SQL: ".mysqli_error($connection)."');";
    }
    
    //Snížit počet obrázků u přírodniny
    //Snížit počet obrázků u části
    //Odstranění všech hlášení vztahujících se k obrázku
    
    //Tyto tři úkony zajišťuje spoušť (trigger) nastavená na SQL serveru
