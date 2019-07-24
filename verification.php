<?php
    session_start();

	require 'ACCESSCODE.php';
	include 'httpStats.php'; //Zahrnuje connect.php
	include 'logger.php';
	$ip = $_SERVER['REMOTE_ADDR'];
	
	if (!isset($_SESSION['user']) && $redirectOut == true)
	{
		//Přesměrovávání na autorizační stránku
		echo "<script type='text/javascript'>location.href = 'index.php';</script>";
		filelog("Uživatel ($ip) byl přesměrován na ověřovací stránku.");
		die();
	}
	else if(isset($_SESSION['user']) && $redirectIn == true)
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