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
    $query = "SELECT obrazky_id,prirodniny_id,casti_id FROM obrazky WHERE zdroj='$url' LIMIT 1";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        echo "alert('Nastala chyba SQL: ".mysqli_error($connection)."');";
    }
    $result = mysqli_fetch_array($result);
    $picId = $result['obrazky_id'];
    $naturalId = $result['prirodniny_id'];
    $cId = $result['casti_id'];
    
    //Odstavení obrázku
    $query = "UPDATE obrazky SET povoleno = 0 WHERE obrazky_id=$picId LIMIT 1";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        echo "alert('Nastala chyba SQL: ".mysqli_error($connection)."');";
    }
    
    //Snížit počet obrázků u přírodniny
    $query = "UPDATE prirodniny SET obrazky = obrazky-1 WHERE prirodniny_id=$naturalId LIMIT 1";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        echo "alert('Nastala chyba SQL: ".mysqli_error($connection)."');";
    }
    
    //Snížit počet obrázků u poznávačky
    $query = "UPDATE casti SET obrazky = obrazky-1 WHERE casti_id=$cId LIMIT 1";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        echo "alert('Nastala chyba SQL: ".mysqli_error($connection)."');";
    }
  
    //Odstranění všech hlášení vztahujících se k obrázku
    $query = "DELETE FROM hlaseni WHERE obrazky_id=$picId";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        echo "alert('Nastala chyba SQL: ".mysqli_error($connection)."');";
    }
