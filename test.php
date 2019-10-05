<?php
	$redirectIn = false;
	$redirectOut = true;
	require 'php/included/verification.php';    //Obsahuje session_start();
    
	if (!isset($_SESSION['current']))	//Poznávačka nenastavena --> přesměrování na stránku s výběrem
	{
		echo "<script type='text/javascript'>location.href = 'list.php';</script>";
	}
?>
<html>
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width" />
		<link rel="stylesheet" type="text/css" href="css/css.css">
		<script type="text/javascript" src="jScript/test.js"></script>
		<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
		<link rel="icon" href="images/favicon.ico">
		<link rel="apple-touch-icon" sizes="180x180" href="images/apple-touch-icon.png">
		<link rel="icon" type="image/png" sizes="32x32" href="images/icon-32x32.png">
		<link rel="icon" type="image/png" sizes="16x16" href="images/icon-16x16.png">
		<link rel="manifest" href="manifest.json">
		<link rel="mask-icon" href="images/safari-pinned-tab.svg" color="#ffc835">
		<meta name="theme-color" content="#ffffff">
		<title>Vyzkoušet se</title>
	</head>
	<body>
    <div class="container">
        <header>
            <h1>Vyzkoušet se</h1>
        </header>
    	<main class="basic_main">
    		<fieldset>
    			<img id="image" class="img" src="images/imagePreview.png">
    			<div id="inputOutput">
    				<form onsubmit="answer(event)" id="answerForm">
    					<input type=text class="text" id="textfield" autocomplete="off" placeholder="Zadejte odpověď">
    					<input type=submit class="button" value="OK" />
    				</form>
    				<span id="correctAnswer">Správně!</span>
    				<div id="wrongAnswer">
    					<span id="wrong1">Špatně!</span><br>
    					<span id="wrong2">Správná odpověď je </span>
    					<span id="serverResponse"></span>
    			    </div>
    			<button onclick="next()" class="button" id="nextButton">Další</button>
    			</div>
    			<button onclick="reportImg(event)" id="reportButton" class="button">Nahlásit</button>
    			<select id="reportMenu" class="text">
    				<option>Obrázek se nezobrazuje správně</option>
    				<option>Obrázek zobrazuje nesprávnou přírodninu</option>
    				<option>Obrázek obsahuje název přírodniny</option>
    				<option>Obrázek má příliš špatné rozlišení</option>
    				<option>Obrázek porušuje autorská práva</option>
    			</select>
    			<button onclick="submitReport(event)" id="submitReport" class="button">Odeslat</button>
    			<button onclick="cancelReport(event)" id="cancelReport" class="button">Zrušit</button>
    		</fieldset>
    		<a href="menu.php"><button class="button">Zpět</button></a>
		</main>
    </div>
		<footer>
			<div id="help" class="footerOption"><a target='_blank' href="https://github.com/HonzaSTECH/Poznavacky/wiki">Nápověda</a></div>
			<div id="issues" class="footerOption" onclick="showLogin()"><a target='_blank' href="https://github.com/HonzaSTECH/Poznavacky/issues/new/choose">Nalezli jste problém?</a></div>
			<div class="footerOption"><a target='_blank' href='https://github.com/HonzaSTECH/Poznavacky/blob/master/documents/TERMS_OF_SERVICE.md'>Podmínky služby</a></div>
			<div id="about" class="footerOption">&copy Štěchy a Eksyska, 2019</div>
         	<script>
             	function showLogin()
             	{
             		alert("Přihlašovací údaje pro nahlašování chyby:\nJméno: gjvj\nHeslo: poznavacky71");
             	}
         	</script>
         </footer>
	</body>
	<script>
		getRequest("getRandomPic.php", showPic);
	</script>
</html>
