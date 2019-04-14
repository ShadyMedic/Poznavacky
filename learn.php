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
		<script type="text/javascript" src="learn.js"></script>
		<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
		<title>Učit se</title>
	</head>
	<body>
        <header>
            <h1>Učit se</h1>
        </header>
		<main class="basic_main">
			<fieldset>
				<div class="prikaz">Vyberte si přírodninu, jejíž obrázky si chcete prohlížet. Na další nebo předchozí přírodninu můžete přejít rychle pomocí tlačítek.</div><!-- div místo nadpisu, class "prikaz"-->
				<select onchange="sel()" id="dropList" class="text"><!--class "text"-->
					<option value="" selected disabled hidden></option>
					<?php 
						//Vypisování přírodnin
						$table = $_SESSION['current'][0].'seznam';
						
						include 'connect.php';
						$query = "SELECT * FROM $table";
						$result = mysqli_query($connection, $query);
						while($row = mysqli_fetch_array($result))
						{
							$name = $row['nazev'];
							echo "<script>naturalList.push('$name');</script>";
							echo "<option>$name</option>";
						}
					?>
				</select>
				<br><!--br na zalomení řádku-->
				<button onclick="prev(event)" class="button">Předchozí přírodnina</button><!-- class "button"-->
				<button onclick="next(event)" class="button">Následující přírodnina</button><!-- class "button"-->
			</fieldset>
			<fieldset>
				<table>
					<tr>
						<td>
							<button onclick="prevImg()" id="prevImg"><img src="arrow.png" style="transform: rotate(180deg);" /></button>
						</td>
						<td>
							<img id="image" class="img" src="imagePreview.png"><!-- class "img"-->
						</td>
						<td>
							<button onclick = "nextImg()" id="nextImg"><img src="arrow.png" /></button>
						</td>
					</tr>
				</table>
				<button onclick="reportImg(event)" id="reportButton" class="buttonDisabled" disabled>Nahlásit</button><!-- class "button" -->
				<select id="reportMenu" class="text"><!-- class "text"-->
					<option>Obrázek se nezobrazuje správně</option>
					<option>Obrázek zobrazuje nesprávnou přírodninu</option>
					<option>Obrázek obsahuje název přírodniny</option>
					<option>Obrázek má příliš špatné rozlišení</option>
					<option>Obrázek porušuje autorská práva</option>
				</select>
				<button onclick="submitReport(event)" id="submitReport" class="button">Odeslat</button><!-- class "button" -->
				<button onclick="cancelReport(event)" id="cancelReport" class="button">Zrušit</button><!-- class "button" -->
			</fieldset>
			<a href="menu.php"><button class="button">Zpět</button></a><!-- class "button"-->
	    </main>
	</body>
</html>