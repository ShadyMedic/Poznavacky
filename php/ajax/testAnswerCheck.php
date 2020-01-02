<?php
    function isCorrect($userAnswer, $correctAnswer)
    {
        $userAnswer = removeDiacritic(strtolower($userAnswer)."××");
        $correctAnswer = removeDiacritic(strtolower($correctAnswer)."××");
        
        if ($userAnswer === $correctAnswer)
        {
            //Odpověď bez překlepů
            //echo "NO TYPOS<br>";
            return true;
        }
        
        $errors = 0;
        
        for ($i = 0; $i < strlen($correctAnswer)-2; $i++)
        {
            if ($userAnswer[$i] !== $correctAnswer[$i])    //Neshodný znak
            {
                if ($userAnswer[$i] == $correctAnswer[$i+1] && $userAnswer[$i+1] == $correctAnswer[$i+2])    //Chybějící znak
                {
                    //echo "Missing character on position $i<br>";
                    $userAnswer = substr($userAnswer, 0, $i).$correctAnswer[$i].substr($userAnswer,$i);
                    $errors++;
                }
                
                else if ($userAnswer[$i+1] == $correctAnswer[$i] && $userAnswer[$i+2] == $correctAnswer[$i+1])    //Přebývající znak
                {
                    //echo "Unneccessary character on position $i<br>";
                    $userAnswer = substr($userAnswer, 0, $i).substr($userAnswer, $i+1);    //Odstraňování přebývajícího znaku
                    $errors++;
                }
                
                else    //Špatný znak
                {
                    //echo "Wrong character on position $i<br>";
                    $userAnswer = substr($userAnswer, 0, $i).$correctAnswer[$i].substr($userAnswer, $i+1);    //Oprava špatného znaku
                    $errors++;
                }
            }
        }
        
        if (($errors / (strlen($userAnswer) - 2)) > 0.334)
        {
            //echo "TOO MANY TYPOS<br>";
            return false;
        }
        
        //echo "FEW TYPOS<br>";
        return true;
    }

    function removeDiacritic($str)
    {
        $str = str_ireplace(['á','ě','é','í','ó','ú','ů','ý','č','ď','ň','ř','š','ť','ž'],['a','e','e','i','o','u','u','y','c','d','n','r','s','t','z'],$str);
        
        return $str;
    }
    
/*--------------------------------------------------------------------------*/

    session_start();
    
    include '../included/httpStats.php'; //Zahrnuje connect.php

    $userAnswer = $_POST['ans'];
    $correctAnswer = @$_SESSION['testAnswer'];
    //Nulování správné odpovědi (aby nebylo možné farmit body za uhodnuté obrázky opakováním requestu)
    unset($_SESSION['testAnswer']);
    
    if (empty($correctAnswer))      //Pokud není žádná správná odpověď, odpověď není správná
    {
        die();
    }
    if (isCorrect($userAnswer, $correctAnswer))
    {
        //Uživatel odpověděl správně
        $_SESSION['user']['guessedPics'] = ++$_SESSION['user']['guessedPics'];
        $username = $_SESSION['user']['name'];
        $query = "UPDATE uzivatele SET uhodnute_obrazky = uhodnute_obrazky + 1 WHERE jmeno = '$username'";
        $result = mysqli_query($connection, $query);
        if (!$result)
        {
            $err = mysqli_error($connection);
            die("swal('Vyskytla se neočekávaná chyba. Kontaktujte prosím správce a uveďte tuto chybu ve svém hlášení:','".mysqli_real_escape_string($connection, $err)."', 'error');");
        }
    }
