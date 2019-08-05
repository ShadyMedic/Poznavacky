<?php
    session_start();
    
    include 'httpStats.php'; //Zahrnuje connect.php

    $userAnswer = $_POST['ans'];
    $correctAnswer = @$_SESSION['testAnswer'];
    //Nulování správné odpovědi (aby nebylo možné farmit body za uhodnuté obrázky opakováním requestu)
    unset($_SESSION['testAnswer']);
    
    if ($userAnswer === $correctAnswer)
    {
        //Uživatel odpověděl správně
        $_SESSION['user']['guessedPics'] = ++$_SESSION['user']['guessedPics'];
        $newScore = $_SESSION['user']['guessedPics'];
        $username = $_SESSION['user']['name'];
        $query = "UPDATE uzivatele SET uhodnuteObrazky = $newScore WHERE jmeno = '$username'";
        $result = mysqli_query($connection, $query);
        if (!$result)
        {
            $err = mysqli_error($connection);
            die("swal('Vyskytla se neočekávaná chyba. Kontaktujte prosím správce a uveďte tuto chybu ve svém hlášení:','".mysqli_real_escape_string($connection, $err)."', 'error');");
        }
    }