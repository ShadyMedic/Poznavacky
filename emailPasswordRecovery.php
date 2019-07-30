<?php 
    session_start();
    
    include 'httpStats.php'; //Zahrnuje connect.php
?>
<html>
	<head>
		<meta charset="UTF-8">
		<!-- <link rel="stylesheet" type="text/css" href="css.css"> -->
		<script type="text/javascript" src="emailPasswordRecovery.js"></script>
		<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
		<title>Obnova hesla</title>
	</head>
	<body>
<?php
    $token = $_GET['token'];
    
    //Mazání vyexpirovaných kódů z databáze
    $query = "DELETE FROM obnovenihesel WHERE (vytvoreno < (NOW() - INTERVAL 86400 SECOND))";   //86 400 s = 1 den
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
    $query = "SELECT uzivatel_id FROM obnovenihesel WHERE kod='".md5($token)."' LIMIT 1";
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
		<div>
			<input type=password maxlength=31 placeholder="Nové heslo" id="pass" required=true />
			<input type=password maxlength=31 placeholder="Nové heslo znovu" id="repass" required=true />
			<button onclick="changePassword()">Změnit heslo</button>
		</div>
	</body>
</html>
