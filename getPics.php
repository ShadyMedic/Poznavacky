<?php
	session_start();

	include 'httpStats.php'; //Zahrnuje connect.php
	include 'logger.php';

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
	$table = $_SESSION['current'][0].'seznam';
	$pName = $_SESSION['current'][1];

	$query = "SELECT id,obrazky FROM $table WHERE nazev='$name'";
	$result = mysqli_query($connection, $query);
	if ($result && mysqli_num_rows($result) > 0){$result = mysqli_fetch_array($result);}
	else{die("swal('Neplatný název!','','error');");}
	$id = $result['id'];
	$amount = $result['obrazky'];

	//Úprava čísla aktuálního obrázku
	while($number < 0){$number += $amount;}
	if($amount > 0){$number %= $amount;}

	//Získávání URL obrázku
	$table = $_SESSION['current'][0].'obrazky';

	$query = "SELECT zdroj FROM $table WHERE prirodninaId=$id AND povoleno=1";
	$result = mysqli_query($connection, $query);
	if (gettype($result) !== "object" || mysqli_num_rows($result) <= 0){die("images/noImage.png");}
	for($i = 0; $i <= $number; $i++)
	{
		$resultArr = mysqli_fetch_array($result);
	}
	$resultArr = $resultArr['zdroj'];
	$username = $_SESSION['user']['name'];
	filelog("K uživateli $username byl odeslán obrázek pro učící stránku pro poznávačku $pName.");
	echo $resultArr;
