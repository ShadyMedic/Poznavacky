<?php
    session_start();
    
	include 'httpStats.php'; //Zahrnuje connect.php
	include 'logger.php';
	$ip = $_SERVER['REMOTE_ADDR'];
	
	if ($redirectOut == true && !isset($_SESSION['user']))
	{
		//Přesměrovávání na autorizační stránku
	    filelog("Uživatel ($ip) byl přesměrován na ověřovací stránku.");
	    header("Location: index.php");
		die();
	}
	else if($redirectIn == true && (isset($_SESSION['user']) || isset($_COOKIE['instantLogin'])))
	{
	    if (isset($_SESSION['user']))
	    {
    		//Přesměrovávání na domovskou stránku
	        filelog("Uživatel ($ip) byl ověřen a přesměrován do systému.");
	        header("Location: list.php");
    		die();
	    }
	    else
	    {
	        //Mazání vyexpirovaných sezení
	        $query = "DELETE FROM sezeni WHERE (vytvoreno < (NOW() - INTERVAL 2592000 SECOND))";
	        $result = mysqli_query($connection, $query);
	        if (!$result)
	        {
	            header("Location: errSql.html");
	            die();
	        }
	        
	        //Kontrola správnosti instantLogin cookie
	        $code = $_COOKIE['instantLogin'];
	        $query = "SELECT uzivatel_id FROM sezeni WHERE kod_cookie='".md5($code)."' LIMIT 1";
	        $result = mysqli_query($connection, $query);
	        if (mysqli_num_rows($result) > 0)
	        {
	            //Přesměrování do systému
	            $userId = mysqli_fetch_array($result)['uzivatel_id'];
	            $_SESSION['user'] = $userId;
	            filelog("Uživatel ($ip) byl ověřen souborem cookie a přesměrován do systému");
	            header("Location: list.php");
	            die();
	        }
	        else
	        {
	            //Mazání neplatného cookie
	            setcookie('instantLogin', '', 0, '/');
	            $_COOKIE['instantLogin'] = NULL;
	        }
	    }
	}
	else
	{
		//Žádné přesměrování
	}