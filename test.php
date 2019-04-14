<html>
	<head>
		<meta charset="UTF-8">
		<link rel="stylesheet" type="text/css" href="css.css">
		<script type="text/javascript" src="test.js"></script>
		<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
		<title>Vyzkoušet se</title>
	</head>
	<body>
		<?php
			$redirectIn = false;
			$redirectOut = true;
			require 'verification.php';
		?>
        <header>
            <h1>Vyzkoušet se</h1>
        </header>
		<main class="basic_main">
			<fieldset>
				<img id="image" class="img" src="imagePreview.png">
				<div id="inputOutput">
					<form onsubmit="answer(event)" id="answerForm">
						<input type=text class="text" id="textfield" autocomplete="off" placeholder="Zadejte odpověď">
						<input type=submit class="button" value="OK" />
					</form>
					<span id="correctAnswer">Správně!</span>
					<div id="wrongAnswer">
						<span id="wrong1">Špatně!</span>
						<br>
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
	</body>
	<script>
		getRequest("getRandomPic.php", showPic);
	</script>
</html>