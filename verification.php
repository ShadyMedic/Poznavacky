<?php
	require 'ACCESSCODE.php';
	include 'httpStats.php'; //Zahrnuje connect.php
	include 'logger.php';
	$ip = $_SERVER['REMOTE_ADDR'];
	
	//Ověřování, zda se již daná IP adresa někdy úspěšně přihlásila.
	//$query = "SELECT * FROM uzivatele WHERE ip='$ip' AND pokusy=3";
	//$result = mysqli_query($connection, $query);
	$auth = null;
	if(isset($_COOKIE['token']) && $_COOKIE['token'] == ACCESSCODE){$auth = true;}
	else if(isset($_COOKIE['token'])){$auth = false;}
	else{$auth = false;}
	
	if ($auth == false && $redirectOut == true)
	{
		//Přesměrovávání na autorizační stránku
		echo "<script type='text/javascript'>location.href = 'index.php';</script>";
		filelog("Uživatel ($ip) byl přesměrován na ověřovací stránku.");
		die();
	}
	else if($auth == true && $redirectIn == true)
	{
		//Přesměrovávání na domovskou stránku
		echo "<script type='text/javascript'>location.href = 'list.php';</script>";
		filelog("Uživatel ($ip) byl ověřen a přesměrován do systému.");
		die();
	}
	else
	{
		//Žádné přesměrování
	}