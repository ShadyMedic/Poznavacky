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
    
    $username = mysqli_real_escape_string($connection, $username);
    
    //Hledání účtu se zadaným jménem
    $query = "SELECT id,heslo FROM uzivatele WHERE jmeno='$user' LIMIT 1";
    $result = mysqli_query($connection, $query);
    if (empty(mysqli_num_rows($result)))    //Uživatel nenalezen
    {
        echo "alert('Neplatný uživatel.');";
    }
    
    $userId = $result['id'];
    
    $query = "";
    $query .= "DELETE FROM zadostijmena WHERE puvodni='$user' LIMIT 1;";        //Odstranění podaných žádostí o změnu jména
    $query .= "DELETE FROM obnovenihesel WHERE uzivatel_id=$userId LIMIT 1;";   //Odstranění kódů k obnovení hesla
    $query .= "DELETE FROM sezeni WHERE uzivatel_id=$userId;";                  //Odstranění kódů instalogin cookies
    $query .= "DELETE FROM uzivatele WHERE jmeno='$user'; LIMIT 1";             //Odstranění samotného účtu
    
    $result = mysqli_multi_query($connection, $query);
    if (!$result)
    {
        echo "alert('Nastala chyba SQL: ".mysqli_error($connection)."');";
    }
