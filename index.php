<?php
	$redirectIn = true;
	$redirectOut = false;
	include 'verification.php';
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
		<link rel="stylesheet" type="text/css" href="css.css">
		<script type="text/javascript" src="index.js"></script>
		<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
        <title>Ověření</title>
    </head>
    <body id="root">
    <div class="container">
		<main id="main_kod">
			<div id="kod"> 
				<span id="span_kod">Zadejte ověřovací kód:</span><br>
				<form onsubmit="validate(event)">
				   <input type=text maxlength=8 class="text" id="kod_input"><br>
				   <input type=submit value="Potvrdit" class="confirm button">
				</form> 
			</div>
		</main>
	</div>
	<footer id="cookiesAlert">
		<span>Tyto stránky využívají ke své funkci soubory cookie. Používáním stránek souhlasíte s ukládáním souborů cookie na vašem zařízení.</span>
		<div id="cookiesAlertCloser" onclick="hideCookies">×</div>
	</footer>
    </body>
</html>