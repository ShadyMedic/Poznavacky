<?php
	session_start();
	
	include 'connect.php';
	include 'logger.php';
	$ip = $_SERVER['REMOTE_ADDR'];
	
	$table = $_SESSION['current'][0].'obrazky';
	
	$query = "SELECT COUNT(*) AS c FROM $table";
	$result = mysqli_query($connection, $query);
	$result =  mysqli_fetch_array($result);
	$amount = $result['c'];
	
	$number = rand(1,$amount);
	
	$query = "SELECT zdroj,prirodninaId FROM $table";
	$result = mysqli_query($connection, $query);
	for ($i = 0; $i < $number; $i++)
	{
		$resultArr = mysqli_fetch_array($result);
	}
	
	echo $resultArr['zdroj'];
	echo "¶";
	
	$id = $resultArr['prirodninaId'];
	$table = $_SESSION['current'][0].'seznam';
	$pName = $_SESSION['current'][1];
	
	$query = "SELECT nazev FROM $table WHERE id=$id";
	$result = mysqli_query($connection, $query);
	$result =  mysqli_fetch_array($result);
	
	filelog("Na adresu $ip byl odeslán obrázek pro zkoušecí stránku pro poznávačku $pName.");
	echo $result['nazev'];
?>