<?php
    //Nastavování současné části
    $pId = @$_COOKIE['current'];
    
    $everything = false;
    
    //Zjištění, zda se nejedná o výběr všech částí v dané poznávačce
    if (strpos($pId,','))
    {
        //Zjištění jmena poznávačky
        include 'php/included/connect.php';
        $pId = mysqli_real_escape_string($connection, $pId);
        if (!empty($pId))
        {
            $everything = true;
            $firstPartId = explode(',',$pId)[0];
            $query = "SELECT poznavacky_id,nazev FROM poznavacky WHERE poznavacky_id=(SELECT poznavacky_id FROM casti WHERE casti_id=$firstPartId LIMIT 1) LIMIT 1";
            $result = mysqli_query($connection, $query);
            if (!$result){echo mysqli_error($connection);}
            $result = mysqli_fetch_array($result);
            $pName = $result['nazev'].' - Vše';
            $pId = $result['poznavacky_id'];
        }
    }
    else
    {
        //Zjištění jmena části
        include 'php/included/connect.php';
        $pId = mysqli_real_escape_string($connection, $pId);
        if (!empty($pId))
        {
            $query = "SELECT nazev FROM casti WHERE casti_id=$pId LIMIT 1";
            $result = mysqli_query($connection, $query);
            $pName = mysqli_fetch_array($result);
            $pName = $pName['nazev'];
        }
    }
    //Mazání cookie current
    setcookie("current", "", time()-3600);
    
    if (!empty($pId))	//Část zvolena
    {
        $pArr = array($pId, $pName, $everything);
        $_SESSION['current'] = $pArr;
    }
    else if (!isset($_SESSION['current']))	//Část nezvolena ani nenastavena --> přesměrování na stránku s výběrem
    {
        header("Location: list.php");
    }