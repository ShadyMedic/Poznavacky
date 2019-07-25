<?php
session_start();

$username = $_SESSION['name'];

//Odhlašování
unset($_SESSION['user']);

//Odstraňování instantLogin cookie
setcookie('instantLogin','',0,'/');
$_COOKIE['instantLogin'] = NULL;

fileLog("Uživatel $username se odhlásil");

header("Location: index.php");