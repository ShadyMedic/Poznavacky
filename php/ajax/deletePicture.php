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
    $url = $_POST['to'];
    
    $url = mysqli_real_escape_string($connection, $url);
    
    //Získávíní ID obrázku (aby bylo možné smazat všechna hlášení, která se k němu vztahují)
    $query = "SELECT id FROM obrazky WHERE zdroj='$url' LIMIT 1";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        echo "alert('Nastala chyba SQL: ".mysqli_error($connection)."');";
    }
    $result = mysqli_fetch_array($result);
    $picId = $result['id'];
    
    //Odstranění obrázku
    $query = "DELETE FROM obrazky WHERE zdroj='$url' LIMIT 1";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        echo "alert('Nastala chyba SQL: ".mysqli_error($connection)."');";
    }
    
    
    
    //Odstranění všech hlášení vztahujících se k obrázku
    $query = "DELETE FROM hlaseni WHERE obrazekId=$picId";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        echo "alert('Nastala chyba SQL: ".mysqli_error($connection)."');";
    }
    
