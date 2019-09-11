<?php
    session_start();

    include 'httpStats.php'; //Zahrnuje connect.php
    include 'logger.php';
    
    $ip = $_SERVER['REMOTE_ADDR'];
    $loggedUser = $_SESSION['user']['name'];
    $user = $_POST['newName'];
    $pass = $_POST['oldPass'];
    
    if ($loggedUser !== $user)
    {
        echo "swal('Varování','Vypadá to, že jste upravil strukturu webové stránky pomocí nástrojů pro vývojáře. Za takových podmínek nemůže služba správně pracovat. Prosíme, vyvarujte se v budoucnosti takovým úpravám.','warning');";
        filelog("Uživatel $loggedUser se pokusil odstranit účet uživatele $user z IP adresy $ip.");
        die();
    }
    
    //Ochrana proti SQL injekci
    $user = mysqli_real_escape_string($connection, $user);
    
    //Hledání účtu se zadaným jménem
    $query = "SELECT id,heslo FROM uzivatele WHERE jmeno='$user' LIMIT 1";
    $result = mysqli_query($connection, $query);
    if (empty(mysqli_num_rows($result)))    //Uživatel nenalezen
    {
        fileLog("Uživatel se pokusil odstranit neexistující účet ($user) z IP adresy $ip");
        
        echo "swal('Něco se pokazilo.','Zkuste to prosím později, nebo se zkuste odhlásit a znovu přihlásit.','error')";
        filelog("Uživatel $loggedUser se pokusil odstranit svůj účet z IP adresy $ip, ale neuspěl kvůli neplatnému jménu.");
        die();
    }
    
    //Kontrola správnosti hesla
    $result = mysqli_fetch_array($result);
    if (password_verify($pass, $result['heslo']))   //Heslo je správné
    {
        $userId = $result['id'];
        
        $query = "";
        $query .= "DELETE FROM zadostijmena WHERE puvodni='$user' LIMIT 1;";        //Odstranění podaných žádostí o změnu jména
        $query .= "DELETE FROM obnovenihesel WHERE uzivatel_id=$userId LIMIT 1;";   //Odstranění kódů k obnovení hesla
        $query .= "DELETE FROM sezeni WHERE uzivateů_id=$userId;";                  //Odstranění kódů instalogin cookies
        $query .= "DELETE FROM uzivatele WHERE jmeno='$user'; LIMIT 1";             //Odstranění samotného účtu
        
        $result = mysqli_multi_query($connection, $query);
        if (!$result)
        {
            echo "location.href = 'errSql.html';";
            
            $err = mysqli_error($connection);
            filelog("Vyskytla se SQL chyba při odstraňování uživatelského účtu: $err.");
            
            die();
        }
        else
        {
            filelog("Uživatel $user odstranil svůj účet z IP adresy $ip.");
            header('Location: logout.php');
        }
    }