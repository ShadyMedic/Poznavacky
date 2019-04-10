<?php
session_start();

$name = $_GET['name'];
$number = $_GET['number'];
include 'connect.php';
include 'logger.php';

//Kontrola zda je vybrána nějaká přírodnina
if ($name === "undefined"){die("imagePreview.png");}

//Zjišťování počtu obrázků
$table = $_SESSION['current'][0].'seznam';
$pName = $_SESSION['current'][1];

$query = "SELECT id,obrazky FROM $table WHERE nazev='$name'";
$result = mysqli_query($connection, $query);
if (gettype($result) === "object"){$result = mysqli_fetch_array($result);}
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
if (gettype($result) !== "object" || mysqli_num_rows($result) <= 0){die("noImage.png");}
for($i = 0; $i <= $number; $i++)
{
	$resultArr = mysqli_fetch_array($result);
}
$resultArr = $resultArr['zdroj'];
$ip = $_SERVER['REMOTE_ADDR'];
filelog("Na adresu $ip byl odeslán obrázek pro učící stránku pro poznávačku $pName.");
echo $resultArr;