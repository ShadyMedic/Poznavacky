<?php
require 'ACCESSCODE.php';
define('ATTEMPTS', 3);
define('DELAY', 1800); //1800 s = 0.5 h

$ip = $_SERVER['REMOTE_ADDR'];

include 'httpStats.php'; //Zahrnuje connect.php
include 'logger.php';
$query = "SELECT pokusy,dalsiPokus FROM uzivatele WHERE ip='$ip'";
$sql = mysqli_query($connection, $query);
try
{
	$sql = mysqli_fetch_array($sql);
}
catch(Exception $e)
{
	$sql = false;
}
if (gettype($sql) != "array")
{
	$query = "INSERT INTO uzivatele VALUES ('$ip', ".ATTEMPTS.", ".time().")";
	mysqli_query($connection, $query);
	$pokusy = ATTEMPTS;
	$cas = 0;
}
else
{
	$pokusy = $sql['pokusy'];
	$cas = $sql['dalsiPokus'];
}

$cas -= time();
if ($pokusy <= 0 && $cas > 0)
{
	$attempts = ATTEMPTS;
	echo "swal('Zadali jste $attempts krát po sobě špatný kód a byl vám tak dočasně odebrán přístup. Zkuste to znovu za $cas sekund.', '', 'error');";
	filelog("Zablokovaný uživatel ($ip) se pokusil o přihlášení.");
	die();
}

$code = $_GET['token'];
setcookie('token', $code, time() + 30 * 86400);
if ($code == ACCESSCODE)
{
	$_COOKIE['token'] = ACCESSCODE;
	echo 'location.href = "list.php";';
	$query = "UPDATE uzivatele SET pokusy=".ATTEMPTS.",dalsiPokus=".time()." WHERE ip='$ip'";
	mysqli_query($connection, $query);
	filelog("Nový uživatel se přihlásil z IP adresy $ip");
}
else
{
	$left = $pokusy - 1;
	$dalsiPokus = time() + DELAY;
	echo "swal('Zadali jste špatný kód. Před dočasným zablokováním přístupu vám zbývá/jí $left pokus/y.', '', 'warning');";
	$query = "UPDATE uzivatele SET pokusy=$left,dalsiPokus=$dalsiPokus WHERE ip='$ip'";
	mysqli_query($connection, $query);
	filelog("Nepřihlášený uživatel ($ip) zadal špatný ověřovací kód.");
}