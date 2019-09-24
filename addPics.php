<?php
	$redirectIn = false;
	$redirectOut = true;
	require 'verification.php';    //Obsahuje session_start();
	
	if (!isset($_SESSION['current']))	//Poznávačka nenastavena --> přesměrování na stránku s výběrem
	{
		echo "<script type='text/javascript'>location.href = 'list.php';</script>";
	}
?>
<html>
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width" />
		<link rel="stylesheet" type="text/css" href="css.css">
		<script type="text/javascript" src="addPics.js"></script>
		<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
		<title>Přidat obrázky</title>
	</head>
	<body>
    <div class="container">
        <header>
            <h1>Přidat obrázky</h1>
        </header>
		<main class="basic_main">
    		<form>
    			<fieldset id="field1">
    				<div class="prikaz">Vyberte přírodninu, kterou chcete nahrát. V závorce je uvedeno množství obrázků dané přírodniny. Nahrávejte prosím především obrázky přírodnin s menším číslem.</div>
    				<select onchange="selected1()" id="dropList" class="text">
    					<option value="" selected disabled hidden></option>
    					<?php 
    						//Vypisování přírodnin
    						$table = $_SESSION['current'][0].'seznam';
    							
    						include 'connect.php';
    						$query = "SELECT * FROM $table ORDER BY nazev,obrazky,id";
    						$result = mysqli_query($connection, $query);
    						while($row = mysqli_fetch_array($result))
    						{
    							$name = $row['nazev'];
    							$count = $row['obrazky'];
    							echo "<option>$name ($count)</option>";
    						}
    					?>
    				</select>
    			</fieldset>
    			<fieldset id="field2">
    				<div id="duckLink_div"><a id="duckLink" target=_blank>  
    					<div><span>Vyhledat na </span><img id="duckLogo" src="duckLogo.png"></div>       
    				</a></div>       
    				<input type=url placeholder="Vložte URL obrázku" id="urlInput" class="text" onkeyup="urlTyped()"/>
    				<button id="urlConfirm" onclick="selected2(event)" class="buttonDisabled" disabled>OK</button>
    			</fieldset>
    				<img id="previewImg" class="img" src="imagePreview.png">
    			<fieldset>
    				<input type=submit value="Přidat" onclick="add(event)" id="sendButton" class="buttonDisabled" disabled />
    				<button id="resetButton" onclick="resetForm(event)" class="button">Reset</button>
    			</fieldset>
    		</form>
    		<a href="menu.php"><button class="button">Zpět</button></a>
    	</main>
    </div>
		<footer>
            <div id="issues" class="footerOption" onclick="showLogin()"><a href="https://github.com/HonzaSTECH/Poznavacky/issues/new/choose">Nalezli jste problém?</a></div>
            <div id="about" class="footerOption">Vytvořili Štěchy a Eksyska v roce 2019</div>
         	<div id="help" class="footerOption"><a href="https://github.com/HonzaSTECH/Poznavacky/wiki">Potřebujete pomoct?</a></div>
         	<script>
             	function showLogin()
             	{
             		alert("Přihlašovací údaje pro nahlašování chyby:\nJméno: gjvj\nHeslo: poznavacky71");
             	}
         	</script>
         </footer>
	</body>
	<script>resetForm();</script>
</html>