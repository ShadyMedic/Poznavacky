<?php
	session_start();
	
	include 'httpStats.php'; //Zahrnuje connect.php
	include 'logger.php';
	
	if (!isset($_SESSION['current']))	//Poznávačka nenastavena --> přesměrování na stránku s výběrem
	{
		echo "location.href = 'list.php';";
		die();
	}
	
	$username = $_SESSION['user']['name'];
	
	
	$table = $_SESSION['current'][0].'seznam';
	
	//Získávání náhodné přírodniny
	/*
	 * Poznámka: tento způsob náhodného výběru je neefektivní pro velké tabulky,
	 * ale pro seznamové tabulky je naprosto v pořádku, protože obsahují maximálně 50 - 100 záznamů.
	 */
	$query = "SELECT id,nazev,obrazky FROM $table WHERE obrazky > 0 ORDER BY RAND() LIMIT 1";
	$result = mysqli_query($connection, $query);
	$result = mysqli_fetch_array($result);
	$answer = $result['nazev'];
	$id = $result['id'];
	
	$table = $_SESSION['current'][0].'obrazky';
	
	//Získávání seznamu obrázků dané přírodniny
	$query = "SELECT zdroj FROM $table WHERE prirodninaId = $id";
	$result = mysqli_query($connection, $query);
	$randIndex = rand(0,mysqli_num_rows($result) - 1);
	mysqli_data_seek($result, $randIndex);
	$row = mysqli_fetch_array($result);
	
	//Odesílání dat
	echo $row['zdroj'];
	echo "¶";
	echo $answer;
    
	//Logování
	$pName = $_SESSION['current'][1];
	filelog("K uživateli $username byl odeslán obrázek pro zkoušecí stránku pro poznávačku $pName.");