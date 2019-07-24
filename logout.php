<?php
session_start();

//Odhlašování
unset($_SESSION['user']);

//Odstraňování instantLogin cookie
setcookie('instantLogin','',0,'/');
$_COOKIE['instantLogin'] = NULL;

header("Location: index.php");