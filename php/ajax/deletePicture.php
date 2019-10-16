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
    $pId = $_POST['oldName'];
    $url = $_POST['to'];
    
    $url = mysqli_real_escape_string($connection, $url);
    $pId = mysqli_real_escape_string($connection, $pId);
    
    //Získávíní ID obrázku (aby bylo možné smazat všechna hlášení, která se k němu vztahují)
    $query = "SELECT id,prirodninaId FROM ".$pId."obrazky WHERE zdroj='$url' LIMIT 1";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        echo "alert('Nastala chyba SQL: ".mysqli_error($connection)."');";
    }
    $result = mysqli_fetch_array($result);
    $picId = $result['id'];
    $naturalId = $result['prirodninaId'];
    
    //Odstranění obrázku
    $tableName = $pId.'obrazky';
    $query = "DELETE FROM $tableName WHERE zdroj='$url' LIMIT 1";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        echo "alert('Nastala chyba SQL: ".mysqli_error($connection)."');";
    }
    
    //Snížit počet obrázků u přírodniny
    $tableName = $pId.'seznam';
    $query = "UPDATE $tableName SET obrazky = obrazky-1 WHERE id=$naturalId LIMIT 1";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        echo "alert('Nastala chyba SQL: ".mysqli_error($connection)."');";
    }
    
    //Snížit počet obrázků u poznávačky
    $query = "UPDATE poznavacky SET obrazky = obrazky-1 WHERE id=$pId LIMIT 1";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        echo "alert('Nastala chyba SQL: ".mysqli_error($connection)."');";
    }
    
    //Odstranění všech hlášení vztahujících se k obrázku
    $tableName = $pId.'hlaseni';
    $query = "DELETE FROM $tableName WHERE obrazekId=$picId";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        echo "alert('Nastala chyba SQL: ".mysqli_error($connection)."');";
    }
    