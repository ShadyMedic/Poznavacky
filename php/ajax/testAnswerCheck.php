<?php
    function isCorrect($userAnswer, $correctAnswer)
    {
        $userAnswer = removeDiacritic(strtolower($userAnswer)."××");
        $correctAnswer = removeDiacritic(strtoupper($correctAnswer)."××");
        
        if ($userAnswer === $correctAnswer)
        {
            //Odpověď bez překlepů
            return true;
        }
        
        $errors = 0;
        
        for ($i = 0; $i < strlen($correctAnswer)-2; $i++)
        {
            if ($userAnswer[$i] !== $correctAnswer[$i])    //Neshodný znak
            {
                if ($userAnswer[$i] == $correctAnswer[$i+1] && $userAnswer[$i+1] == $correctAnswer[i+2])    //Chybějící znak
                {
                    $userAnswer = substr($userAnswer, 0, $i).$correctAnswer[$i].substr($userAnswer,$i);
                    $errors++;
                }
                
                else if ($userAnswer[$i+1] == $correctAnswer[$i] && $userAnswer[$i+2] == $correctAnswer[$i+1])    //Přebývající znak
                {
                    $userAnswer = substr($userAnswer, 0, $i).substr($userAnswer, $i+1);    //Odstraňování přebývajícího znaku
                    $errors++;
                }
                
                else    //Špatný znak
                {
                    $userAnswer = substr($userAnswer, 0, $i).$correctAnswer[$i].substr($userAnswer, $i+1);    //Oprava špatného znaku
                    $errors++;
                }
            }
        }
        
        if (($errors / (strlen($userAnswer) - 2)) > 0.334)
        {
            return false;
        }
        
        return true;
    }

    function removeDiacritic($str)
    {
        $str = str_replace('á','a',$str);
        $str = str_replace('ě','e',$str);
        $str = str_replace('é','e',$str);
        $str = str_replace('í','i',$str);
        $str = str_replace('ó','o',$str);
        $str = str_replace('ú','u',$str);
        $str = str_replace('ů','u',$str);
        $str = str_replace('ý','y',$str);
        $str = str_replace('č','c',$str);
        $str = str_replace('ď','d',$str);
        $str = str_replace('ň','n',$str);
        $str = str_replace('ř','r',$str);
        $str = str_replace('š','s',$str);
        $str = str_replace('ť','t',$str);
        $str = str_replace('ž','z',$str);
        
        return $str;
    }
    
/*--------------------------------------------------------------------------*/

    session_start();
    
    include '../included/httpStats.php'; //Zahrnuje connect.php

    $userAnswer = $_POST['ans'];
    $correctAnswer = @$_SESSION['testAnswer'];
    //Nulování správné odpovědi (aby nebylo možné farmit body za uhodnuté obrázky opakováním requestu)
    unset($_SESSION['testAnswer']);
    
    if (isCorrect($userAnswer, $correctAnswer))
    {
        //Uživatel odpověděl správně
        $_SESSION['user']['guessedPics'] = ++$_SESSION['user']['guessedPics'];
        $newScore = $_SESSION['user']['guessedPics'];
        $username = $_SESSION['user']['name'];
        $query = "UPDATE uzivatele SET uhodnute_obrazky = $newScore WHERE jmeno = '$username'";
        $result = mysqli_query($connection, $query);
        if (!$result)
        {
            $err = mysqli_error($connection);
            die("swal('Vyskytla se neočekávaná chyba. Kontaktujte prosím správce a uveďte tuto chybu ve svém hlášení:','".mysqli_real_escape_string($connection, $err)."', 'error');");
        }
    }
