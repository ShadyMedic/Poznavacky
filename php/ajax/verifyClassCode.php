<?php
    session_start();
    
    include '../included/httpStats.php'; //Zahrnuje connect.php
    include '../included/logger.php';
    
    //Vybrat třídy, ve kterých je již uživatel členem, aby nevznikali duplikovaná členství a třídy, do kterých se nedá dostat pomocí kódu (zamčené třídy) a třídy, do kterých není kód potřeba (veřejné třídy).
    $userId = $_SESSION['user']['id'];
    $userId = mysqli_real_escape_string($connection, $userId);
    $query = "SELECT `tridy_id` FROM `clenstvi` WHERE `uzivatele_id` = $userId UNION SELECT tridy_id FROM tridy WHERE status IN ('locked','public')";
    $result = mysqli_query($connection, $query);
    $badIds = array();
    while ($row = mysqli_fetch_array($result))
    {
        array_push($badIds,$row['tridy_id']);
    }
    
    $code = $_POST['code'];
    $code = mysqli_real_escape_string($connection, $code);
    
    $query = "SELECT tridy_id,nazev FROM tridy WHERE kod = '$code' AND tridy_id NOT IN (".implode(',',$badIds).");";
    $result = mysqli_query($connection, $query);
    if (mysqli_num_rows($result) < 1)
    {
        die("Žádná třída se zadaným vstupním kódem nebyla nalezena");
    }
    $classIds = array();
    $classNames = array();
    while ($row = mysqli_fetch_array($result))
    {
        array_push($classIds, $row['tridy_id']);
        array_push($classNames, $row['nazev']);
    }
    
    $query = "INSERT INTO clenstvi (uzivatele_id,tridy_id) VALUES ";
    for ($i = 0; $i < count($classIds); $i++)
    {
        $query .= '('.$_SESSION['user']['id'].','.$classIds[$i].')';
        if (($i + 1) === count($classIds)){$query .= ';';}
        else {$query .= ',';}
    }
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        echo mysqli_error($connection);
        echo $query;
    }
    
    if (count($classNames) < 2)
    {
        echo 'Do vašich tříd byla přidána třída '.$classNames[0];
    }
    else
    {
        echo 'Do vašich tříd byly přidány třídy ';
        for ($i = 0; $i < count($classNames); $i++)
        {
            echo $classNames[$i];
            if ($i + 2 < count($classNames)){echo ', ';}
            else if ($i + 1 < count($classNames)){echo ' a ';}
        }
    }