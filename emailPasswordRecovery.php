<?php 
    session_start();
    
    include 'php/included/httpStats.php'; //Zahrnuje connect.php
?>
<html>
	<head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width" />
		<link rel="stylesheet" type="text/css" href="css/css.css">
		<script type="text/javascript" src="jScript/emailPasswordRecovery.js"></script>
		<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
		<title>Obnova hesla</title>
	</head>
	<body>
<?php
    $token = $_GET['token'];
    
    //Mazání vyexpirovaných kódů z databáze
    $query = "DELETE FROM obnoveni_hesel WHERE (vytvoreno < (NOW() - INTERVAL 86400 SECOND))";   //86 400 s = 1 den
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        header("Location: errSql.html");
        die();
    }
    
    //Kontrola délky kódu
    if (strlen($token) !== 32)
    {
        echo "Kód obnovy hesla musí mít 32 znaků. Zkontrolujte správnost odkazu.";
        die();
    }

    //Kontrola správnosti kódu
    $query = "SELECT uzivatele_id FROM obnoveni_hesel WHERE kod='".md5($token)."' LIMIT 1";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        header("Location: errSql.html");
        die();
    }
    if (mysqli_num_rows($result) < 1)
    {
        echo "Váš odkaz není platný. Buďto již vypršela jeho platnost, byl již použit, nebo obsahuje špatný kód obnovy hesla.";
        die();
    }
    
    //KÓD JE OK
?>
		<main id="pw_recovery">
			<input class="text" type=password maxlength=31 placeholder="Nové heslo" id="pass" required=true /><br>
			<input class="text" type=password maxlength=31 placeholder="Nové heslo znovu" id="repass" required=true /><br>
			<button class="button" onclick="changePassword()">Změnit heslo</button>
		</main>
	</body>
</html>
