<?php    
    $redirectIn = false;
    $redirectOut = true;
    require 'verification.php';    //Obsahuje session_start();
    
    //Kontrola, zda je uživatel administrátorem.
    $username = $_SESSION['user']['name'];
    $query = "SELECT status FROM uzivatele WHERE jmeno='$username' LIMIT 1";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        header("Location: errSql.html");
        die();
    }
    $status = mysqli_fetch_array($result)['status'];
    if ($status !== 'admin')
    {
        //Zamítnutí přístupu
        header("Location: err403.html");
        die();
    }
    
    //Heslo raději znovu načtu z databáze - nebudu používat hash uložený v $_SESSION['user']['hash']
    $query = "SELECT heslo FROM uzivatele WHERE jmeno='$username' LIMIT 1";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        header("Location: errSql.html");
        die();
    }
    $adminHash = mysqli_fetch_array($result)['heslo'];
    //Kontrola zadaného hesla
    if (isset($_POST['adminPassword']) && !password_verify(@$_POST['adminPassword'], $adminHash))
    {
        echo "<span>Špatné heslo</span>";
    }
    if (!isset($_POST['adminPassword']) || !password_verify(@$_POST['adminPassword'], $adminHash))
    {
        echo "
            <form action='management.php' method=POST>
                <input type=password maxlength=31 placeholder='Zadejte administrátorské heslo' name='adminPassword'>
                <input type=submit value='OK'>
            </form>
        ";
    }
    
    if (password_verify(@$_POST['adminPassword'], $adminHash))
    {
        //Vymaž zadané heslo a počkej na vykreslení stránky
        unset($_POST['adminPassword']);
    }
    else
    {
        //Vymaž zadané heslo
        unset($_POST['adminPassword']);
        
        //Nevykresluj stránku - admin není autorizován
        die();
    }
?>

<!DOCTYPE html>
<html>
    <head>
    <meta charset="UTF-8">
	<script type="text/javascript" src="management.js"></script>
	<link rel="stylesheet" type="text/css" href="private.css">
    <title>Správa služby</title>
	</head>
    <body>
		<nav>
			<a onclick="firstTab()">Tab1</a>
			<a onclick="secondTab()">Tab2</a>
			<a onclick="thirdTab()">Tab3</a>
			<a onclick="fourthTab()">Tab4</a>
		</nav>
		<div id="container">
			<div id="tab1">
				Obsah 1
			</div>
			<div id="tab2">
				Obsah 2
			</div>
			<div id="tab3">
				Obsah 3
			</div>
			<div id="tab4">
				Obsah 4
			</div>
		</div>
	</body>
</html>