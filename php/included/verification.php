<?php
    function tryCookieLogin()
    {
        global $connection;
        
        //Mazání vyexpirovaných sezení
        $query = "DELETE FROM sezeni WHERE (vytvoreno < (NOW() - INTERVAL 2592000 SECOND))";
        $result = mysqli_query($connection, $query);
        if (!$result)
        {
            //echo mysqli_error($connection);
            header("Location: errSql.html");
            die();
        }
        
        //Kontrola správnosti instantLogin cookie
        $code = $_COOKIE['instantLogin'];
        $query = "SELECT uzivatel_id FROM sezeni WHERE kod_cookie='".md5($code)."' LIMIT 1";
        $result = mysqli_query($connection, $query);
        if (mysqli_num_rows($result) > 0)
        {
            
            $userId = mysqli_fetch_array($result)['uzivatel_id'];
            
            //Hledání dat o uživateli
            $query = "SELECT id,jmeno,heslo,email,pridaneObrazky,uhodnuteObrazky,karma,status FROM uzivatele WHERE id = $userId LIMIT 1";
            $result = mysqli_query($connection, $query);
            $result = mysqli_fetch_array($result);
            
            //Ukládání dat
            $userData = [
                'id' => $result['id'],
                'name' => $result['jmeno'],
                'hash' => $result['heslo'],
                'email' => $result['email'],
                'addedPics' => $result['pridaneObrazky'],
                'guessedPics' => $result['uhodnuteObrazky'],
                'karma' => $result['karma'],
                'status' => $result['status']
            ];
            $_SESSION['user'] = $userData;
            
            return true;
        }
        else
        {
            //Mazání neplatného cookie
            setcookie('instantLogin', '', 0, '/');
            $_COOKIE['instantLogin'] = NULL;
            
            return false;
        }
    }
/*------------------------------------------------------------------------------------------------------*/
    session_start();
    
	include 'httpStats.php'; //Zahrnuje connect.php
	include 'logger.php';
	
	if ($redirectOut == true && !isset($_SESSION['user']))
	{
	    //Kontrola přítomnosti instantLogin cookie pro uchování přihlášení
	    if (isset($_COOKIE['instantLogin']) && tryCookieLogin())
	    {
            $userdata = $_SESSION['user'];
            
            //Aktualizace času posledního přihlášení
            $userId = $userdata['id'];
            $query = "UPDATE uzivatele SET posledniPrihlaseni='".date('Y-m-d H:i:s')."' WHERE id=$userId";
            $result = mysqli_query($connection, $query);
            
            $username = $userdata['name'];
            filelog("Uživatel ($username) byl ověřen souborem cookie a bylo obnoveno jeho přihlášení");
	    }
	    else
	    {
		//Přesměrovávání na autorizační stránku
	    	header("Location: index.php");
		die();
	    }
	}
	else if($redirectIn == true && (isset($_SESSION['user']) || isset($_COOKIE['instantLogin'])))
	{
	    if (isset($_SESSION['user']))
	    {   
    		//Přesměrovávání na domovskou stránku
    		$userdata = $_SESSION['user'];
    		
    		//Aktualizace času posledního přihlášení
    		$userId = $userdata['id'];
    		$query = "UPDATE uzivatele SET posledniPrihlaseni='".date('Y-m-d H:i:s')."' WHERE id=$userId";
    		$result = mysqli_query($connection, $query);
    		
    		$username = $userdata['name'];
	        filelog("Uživatel $username byl ověřen a přesměrován do systému.");
	        header("Location: list.php");
    		die();
	    }
	    else if(tryCookieLogin())
	    {
	        //Přesměrovávání na domovskou stránku
	        $userdata = $_SESSION['user'];
	        
	        //Aktualizace času posledního přihlášení
	        $userId = $userData['id'];
	        $query = "UPDATE uzivatele SET posledniPrihlaseni='".date('Y-m-d H:i:s')."' WHERE id=$userId";
	        $result = mysqli_query($connection, $query);
	        
	        $username = $userdata['name'];
	        filelog("Uživatel $username byl ověřen souborem cookie a přesměrován do systému.");
	        header("Location: list.php");
	        die();
	    }
	}
	else
	{
		//Žádné přesměrování
	}
