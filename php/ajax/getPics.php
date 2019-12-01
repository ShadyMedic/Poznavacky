<?php
	session_start();

	include '../included/httpStats.php'; //Zahrnuje connect.php
	include '../included/logger.php';

	if (!isset($_SESSION['current']))	//Poznávačka nenastavena --> přesměrování na stránku s výběrem
	{
		echo "location.href = 'list.php';";
		die();
	}

	$name = $_GET['name'];
	$number = $_GET['number'];

	//Kontrola zda je vybrána nějaká přírodnina
	if ($name === "undefined"){die("images/imagePreview.png");}
    
	//Ochrana před SQL injekcí
	$name = mysqli_real_escape_string($connection, $name);
	
	//Zjišťování počtu obrázků
	$query = "SELECT prirodniny_id,obrazky FROM prirodniny WHERE nazev='$name' LIMIT 1";
	$result = mysqli_query($connection, $query);
	if ($result && mysqli_num_rows($result) > 0){$result = mysqli_fetch_array($result);}
	else{die("swal('Neplatný název!','','error');");}
	$id = $result['prirodniny_id'];
	$amount = $result['obrazky'];

	//Úprava čísla aktuálního obrázku
	while($number < 0){$number += $amount;}
	if($amount > 0){$number %= $amount;}

	//Získávání URL obrázku
	$query = "SELECT zdroj FROM (SELECT obrazky_id,zdroj FROM obrazky WHERE prirodniny_id=$id AND povoleno=1 LIMIT ".($number+1).") AS zdroje ORDER BY obrazky_id DESC LIMIT 1";
	$result = mysqli_query($connection, $query);
	if (gettype($result) !== "object" || mysqli_num_rows($result) <= 0){die("images/noImage.png");}
	$resultArr = mysqli_fetch_array($result);
	$resultArr = $resultArr['zdroj'];
	
	$username = $_SESSION['user']['name'];
	$pName = $_SESSION['current'][1];
	filelog("K uživateli $username byl odeslán obrázek pro učící stránku pro poznávačku $pName.");
	echo $resultArr;
