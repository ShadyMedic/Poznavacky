<?php
	session_start();
	
	include '../included/httpStats.php'; //Zahrnuje connect.php
	include '../included/logger.php';
	
	if (!isset($_SESSION['current']))	//Poznávačka nenastavena --> přesměrování na stránku s výběrem
	{
		echo "location.href = 'list.php';";
		die();
	}
	
	$pId = $_SESSION['current'][0];
	$pId = mysqli_real_escape_string($connection, $pId);
	
	//Získání náhodného čísla v rozmezí 0 až počet přírodnin ve zvolené poznávačce, které mají alespoň jeden nahraný obrázek
	if ($_SESSION['current'][2] === true)
	{
	    //Výběr ze všech částí poznávačky
	   $query = "SELECT CEIL(RAND() *(SELECT COUNT(*) FROM prirodniny WHERE cast IN (SELECT id FROM casti WHERE poznavacka = $pId) AND obrazky > 0))AS randNum";
	}
	else
	{
	    //Výběr přírodniny z konkrétní části
	   $query = "SELECT CEIL(RAND() *(SELECT COUNT(*) FROM prirodniny WHERE cast = $pId AND obrazky > 0))AS randNum";
	}
    $result = mysqli_query($connection, $query);
	if (!$result)
	{
	    echo $query;
	    echo "<br>";
	    echo mysqli_error($connection);
	}
	$result = mysqli_fetch_array($result);
	$rand = $result['randNum'];
	$rand--;   //Odečtení jedničky, aby se zahrnula i první přírodnina a ne neexistující přírodnina po té poslední
	
	//Získání ID a názvu náhodné přírodniny patřící do zvolené poznávačky
	if ($_SESSION['current'][2] === true)
	{
	    //Výběr z přírodnin patřících do celé poznávačky
	    $query = "SELECT id,nazev FROM prirodniny WHERE cast IN (SELECT id FROM casti WHERE poznavacka = $pId) AND obrazky > 0 ORDER BY id ASC LIMIT 1 OFFSET $rand";
	}
	else
	{
	    //Výběr z přírodnin patřících pouze do konkrétní části
	    $query = "SELECT id,nazev FROM prirodniny WHERE cast = $pId AND obrazky > 0 ORDER BY id ASC LIMIT 1 OFFSET $rand";
	}
	$result = mysqli_query($connection, $query);
	if (!$result)
	{
	    echo $query;
	    echo "<br>";
	    echo mysqli_error($connection);
	}
	$result = mysqli_fetch_array($result);
	$id = $result['id'];
	$answer = $result['nazev'];
	
	//Získávání náhodného obrázků dané přírodniny
	$query = "SELECT zdroj FROM obrazky WHERE prirodninaId = $id AND povoleno = 1 ORDER BY RAND() LIMIT 1";
	$result = mysqli_query($connection, $query);
	if (!$result)
	{
	    echo $query;
	    echo "<br>";
	    echo mysqli_error($connection);
	}
	$result = mysqli_fetch_array($result);
	$source = $result['zdroj'];
	
	//Odesílání dat
	echo $source;
	echo "¶";
	echo $answer;
	
	//Nastavování správné odpovědi pro účel možného zvýšení počtu uhodnutých obrázků uživatele
	$_SESSION['testAnswer'] = $answer;
    
	//Logování
	$username = $_SESSION['user']['name'];
	$pName = $_SESSION['current'][1];
	filelog("K uživateli $username byl odeslán obrázek pro zkoušecí stránku pro poznávačku $pName.");
