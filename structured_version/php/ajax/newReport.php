<?php
	session_start();

	include '../included/httpStats.php'; //Zahrnuje connect.php
	include '../included/logger.php';

	if (!isset($_SESSION['current']))	//Poznávačka nenastavena --> přesměrování na stránku s výběrem
	{
		echo "location.href = 'list.php';";
		die();
	}

	$username = $_SESSION['user']['name'];

	$url = $_GET['pic'];
	$reason = $_GET['reason'];
	$info = urldecode($_GET['info']);
	$partId = $_SESSION['current'][0];

	//Ochrana před SQL injekcí
	$url = mysqli_real_escape_string($connection, $url);
	$reason = mysqli_real_escape_string($connection, $reason);
	$info = mysqli_real_escape_string($connection, $info);
	
	if ($info === 'undefined' || empty($info))
	{
	    $info = NULL;
	}
	
	if (!((strlen($info) <= 255 && $reason == 6) ||(strlen($info) <= 31 && $reason == 2) || (strlen($info) === 4 && $reason == 1) || (strlen($info) === 5 && $reason == 1) || $info === NULL))
	{
	    die("swal('Špatná délka doplňkových informací!','','error');");
	}
	
	if ($reason !== "0" && $reason != 1 && $reason != 2 && $reason != 3 && $reason != 4 && $reason != 5 && $reason != 6)
	{
		die("swal('Neplatný důvod!','','error');");
	}
	
	//Získání ID konkrétní části, pokud byly vybrány všechny části poznávačky
	if ($_SESSION['current'][2] === true)
	{
	    $query = "SELECT casti_id FROM obrazky WHERE zdroj = '$url' AND casti_id IN (SELECT casti_id FROM casti WHERE poznavacky_id = $partId) LIMIT 1";
	    $result = mysqli_query($connection, $query);
	    $result = mysqli_fetch_array($result);
	    $partId = $result['casti_id'];
	}
	
	//Získávání id obrázku
	$query = "SELECT obrazky_id FROM obrazky WHERE zdroj='$url' AND casti_id = $partId";
	$result = mysqli_query($connection, $query);
	if (!$result)
	{
	    $err = mysqli_error($connection);
	    die("swal('Vyskytla se neočekávaná chyba. Kontaktujte prosím správce a uveďte tuto chybu ve svém hlášení:','".mysqli_real_escape_string($connection, $err)."', 'error');");
	}
	$result = mysqli_fetch_array($result);
	$picId = $result['obrazky_id'];
	if(empty($picId))
	{
	    die("swal('Neplatný obrázek','','error');");
	}
	
	
	//Zjišťování, zda je již obrázek nahlášen
	$pName = $_SESSION['current'][1];

	$query = "SELECT pocet FROM hlaseni WHERE obrazky_id=$picId AND duvod=$reason AND dalsi_informace='$info'";
	$result = mysqli_query($connection, $query);
	if (gettype($result) !== "object" || mysqli_num_rows($result) <= 0)
	{
		$query = "INSERT INTO hlaseni VALUES (NULL, $picId, $reason, '$info', 1)";	//Přidávání nového hlášení do databáze
	}
	else
	{
		//Přičítání k počtu hlášení v existujícím záznamu
		$result = mysqli_fetch_array($result);
		$newCount = ++$result['pocet'];
		$query = "UPDATE hlaseni SET pocet = $newCount WHERE obrazky_id=$picId AND duvod=$reason AND dalsi_informace='$info'";
	}

	mysqli_query($connection, $query);
	filelog("Uživatel $username nahlásil obrázek s id $picId v poznávačce $pName z důvodu číslo $reason.");
	if (!mysqli_error($connection)){echo "swal('Hlášení zaznamenáno','Obrázek bude co nejdříve zkontrolován. Do té doby bude nadále zobrazován. Nenahlašujte jej prosím vícekrát.','success');";}
	else
	{
	    $err = mysqli_error($connection);
	    echo "swal('Vyskytla se neočekávaná chyba. Kontaktujte prosím správce a uveďte tuto chybu ve svém hlášení:','".mysqli_real_escape_string($connection, $err)."URL: $url - Query: $query', 'error');";
	}
