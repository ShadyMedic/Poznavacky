<?php
	$redirectIn = false;
	$redirectOut = true;
	require 'verification.php';
	
	session_start();
?>
<html>
	<head>
		<meta charset="UTF-8">
		<link rel="stylesheet" type="text/css" href="css.css">
		<script type="text/javascript" src="addPics.js"></script>
		<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
		<title>Přidat obrázky</title>
	</head>
	<body>
		<main class="basic_main">
			<h1>Přidat obrázky</h1>
			<form onsubmit="add(event)">
				<fieldset id="field1">
					<div class="prikaz">Vyberte přírodninu, kterou chcete nahrát. V závorce je uvedeno množství obrázků dané přírodniny. Nahrávejte prosím především obrázky přírodnin s menším číslem.</div><!-- div místo nadpisů, class "prikaz"-->
					<select onchange="selected1()" id="dropList" class="text"><!--class "text"-->
						<option value="" selected disabled hidden></option>
						<?php 
							//Vypisování přírodnin
							$table = $_SESSION['current'][0].seznam;
							
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
					<input type=url placeholder="Vložte URL obrázku" id="urlInput" class="text" onkeyup="urlTyped()"/><!-- class "text"-->
					<button id="urlConfirm" onclick="selected2(event)" class="buttonDisabled" disabled>OK</button><!-- class "button" -->
				</fieldset>
				    <img id="previewImg" class="img" src="imagePreview.png" style="left: 0; right: 0; position: relative; margin: auto; margin-top: 6px;"><!-- class "img"--> 
				<fieldset>
					<input type=submit value="Přidat" id="sendButton" class="buttonDisabled" disabled /><!-- class "button"-->
					<button id="resetButton" onclick="resetForm(event)" class="button">Reset</button><!-- class "button"-->
				</fieldset>
			</form>
			<a href="menu.php"><button class="button">Zpět</button></a><!--class "button"-->
		</main>
	</body>
	<script>resetForm();</script>
</html>