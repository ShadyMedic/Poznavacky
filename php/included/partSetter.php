<?php
    //Nastavování současné části
    $pId = @$_COOKIE['current'];
    $classId = $_SESSION['class'];
    
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
            
            //Kontrola, zda zvolená poznávačka patří do dříve zvolené třídy
            //Tím je znemožněn výběr poznávačky patřící do třídy, v níž uživatel není členem
            $query = "SELECT COUNT(*) AS 'cnt' FROM poznavacky WHERE poznavacky_id = $pId AND tridy_id = $classId";
            if (mysqli_fetch_array(mysqli_query($connection, $query))['cnt'] < 1)
            {
                //Zamítnutí přístupu
                header("Location: err403.html");
                die();
            }
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
            
            //Kontrola, zda zvolená část patří do poznávačky patřící do dříve zvolené třídy
            //Tím je znemožněn výběr části patřící poznávačky, která je součástí třídy, v níž uživatel není členem
            $query = "SELECT COUNT(*) AS 'cnt' FROM casti WHERE casti_id = $pId AND poznavacky_id IN (SELECT poznavacky_id FROM poznavacky WHERE tridy_id = $classId)";
            if (mysqli_fetch_array(mysqli_query($connection, $query))['cnt'] < 1)
            {
                //Zamítnutí přístupu
                header("Location: err403.html");
                die();
            }
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