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

	$name = urldecode($_GET['name']);
	$url = urldecode($_GET['url']);

	if (empty($name)){die("swal('Neplatný název', '', 'error');");}
	if (empty($url)){die("swal('Neplatná adresa', '', 'error');");}
    
	$partId = $_SESSION['current'][0];
	$pName = $_SESSION['current'][1];
	
	//Získat název přírodniny.
	$final = "";
	$arr = str_split($name);
	for ($i = count($arr) - 1; $arr[$i] != '('; $i--){}
	for ($j = 0; $j < $i - 1; $j++){$final .= $arr[$j];}
	$final = mysqli_real_escape_string($connection, $final);

	//Získat ID přírodniny
	$query = "SELECT prirodniny_id FROM prirodniny WHERE nazev='$final'";
	$result = mysqli_query($connection, $query);
	if (mysqli_num_rows($result) > 0)
	{
		$id = mysqli_fetch_array($result);
		$id = $id['prirodniny_id'];
	}
	else
	{
		die("swal('Neplatný název', '', 'error');");
	}

	//Kontrola, zda li daná adresa vede na obrázek
	$urlCopy = $url."@";
	$urlCopy = strtolower($urlCopy);

	if (!(strpos($urlCopy, ".jpg@") || strpos($urlCopy, ".jpeg@") || strpos($urlCopy, ".png@") || strpos($urlCopy, ".gif@") || strpos($urlCopy, ".bmp@") || strpos($urlCopy, ".tiff@")))
	{
		filelog("Uživatel $username se pokusil nahrát obrázek v nesprávném formátu ($url) k přírodnině id $id v poznávačce $pName");
		die("swal('Obrázek musí být ve formátu .jpg, .jpeg, .png, .gif, .bmp nebo .tiff.', '', 'error');");
	}

	//Kontrola duplicitního obrázku
	$url = mysqli_real_escape_string($connection, $url);
	$query = "SELECT obrazky_id FROM obrazky WHERE zdroj='$url'";
	$result = mysqli_query($connection, $query);
	if (mysqli_num_rows($result) > 0)
	{
		filelog("Uživatel $username se pokusil nahrát duplicitní obrázek k přírodnině id $id v poznávačce $pName");
		die("swal('Tento obrázek je již přidán.', '', 'error');");
	}
	
	//Získání ID konkrétní části, pokud byly vybrány všechny části poznávačky
	if ($_SESSION['current'][2] === true)
	{
	    $query = "SELECT casti_id FROM prirodniny WHERE nazev = '$final' LIMIT 1";
	    $result = mysqli_query($connection, $query);
	    $result = mysqli_fetch_array($result);
	    $partId = $result['cast'];
	}
	
	//Vložit obrázek do databáze
	$query = "INSERT INTO obrazky VALUES (NULL, $id, '$url', $partId, 1)";
	$result = mysqli_query($connection, $query);
	if (!$result)
	{
		$err = mysqli_error($connection);
		echo $query;
		filelog("Uživatel $username nemohl nahrát obrázek pro přírodninu $id v poznávačce $pName, protože se vyskytla neočekávaná chyba: $err.");
		die("swal('Vyskytla se neočekávaná chyba. Kontaktujte prosím správce a uveďte tuto chybu ve svém hlášení:','".mysqli_real_escape_string($connection, $err)."', 'error');");
	}
    
	//Zvýšit autorovy obrázku počet nahraných obrázků v databázi
	$_SESSION['user']['addedPics'] = ++$_SESSION['user']['addedPics'];
	$query = "UPDATE uzivatele SET pridane_obrazky = pridane_obrazky + 1 WHERE jmeno = '$username'";
	$result = mysqli_query($connection, $query);
	if (!$result)
	{
	    $err = mysqli_error($connection);
	    filelog("Uživatel $username nemohl nahrát obrázek pro přírodninu $id v poznávačce $pName, protože se vyskytla neočekávaná chyba: $err.");
	    die("swal('Vyskytla se neočekávaná chyba. Kontaktujte prosím správce a uveďte tuto chybu ve svém hlášení:','".mysqli_real_escape_string($connection, $err)."', 'error');");
	}
	
	//Upravit počet obrázků dané přírodniny v tabulce prirodniny
	//Upravit počet obrázků dané přírodniny v tabulce casti
    
	//Tyto dva úkony zajišťuje spoušť (trigger) nastavená na SQL serveru
	
	filelog("Uživatel $username nahrál nový obrázek k přírodnině id $id v poznávačce $pName");
	die("swal('Obrázek úspěšně přidán', '', 'success');");
