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
    <main id="main_kod">
        <div id="kod"> 
            <span id="span_kod">Zadejte ověřovací kód:</span><br>
            <form onsubmit="validate(event)">
	           <input type=text maxlength=8 class="text" id="kod_input">
		   <br>
	           <input type=submit value="Potvrdit" class="confirm button">
            </form>
	    <span id="span_terms">Odesláním kódu souhlasíte s <a target="_blank" href="https://github.com/HonzaSTECH/Poznavacky/blob/master/TERMS_OF_SERVICE.md">podmínkami služby</a>.</span>
        </div>
    </main>   
    </body>
</html>