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
		<link rel="stylesheet" type="text/css" href="css/css.css">
		<script type="text/javascript" src="learn.js"></script>
		<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
		<link rel="icon" href="favicon.ico">
		<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
		<link rel="icon" type="image/png" sizes="32x32" href="/icon-32x32.png">
		<link rel="icon" type="image/png" sizes="16x16" href="/icon-16x16.png">
		<link rel="manifest" href="/manifest.json">
		<link rel="mask-icon" href="/safari-pinned-tab.svg" color="#ffc835">
		<meta name="theme-color" content="#ffffff">
		<title>Učit se</title>
	</head>
	<body onkeypress="keyPressed(event)">
        <div class="container">
        <header>
            <h1>Učit se</h1>
        </header>
    	<main class="basic_main">
    		<fieldset>
    			<div class="prikaz">Vyberte si přírodninu, jejíž obrázky si chcete prohlížet. Na další nebo předchozí přírodninu můžete přejít rychle pomocí tlačítek.</div>
    		  <select onchange="sel()" id="dropList" class="text">
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
    			<br>
    			<button onclick="prev(event)" class="button">
				Předchozí přírodnina
				<span class="key_short">[S]</span>
			</button>
    			<button onclick="next(event)" class="button">
				Následující přírodnina
				<span class="key_short">[W]</span>
			</button>
    		</fieldset>
    		<fieldset>
    			<table>
    				<tr>
					<td class=>
    						<button onclick="prevImg()" class="learn_button" id="prevImg">
							<img src="arrow.png" style="transform: rotate(180deg);" /><br>
                            <span class="key_short">[A]</span>
						    </button>
    					</td>
    					<td>
    						<img id="image" class="img" src="imagePreview.png">
    					</td>
    					<td>
    						<button onclick = "nextImg()" class="learn_button" id="nextImg">
							<img src="arrow.png" /><br>
                            <span class="key_short">[D]</span>
						    </button>
    					</td>
    				</tr>
    			</table>
    			<button onclick="reportImg(event)" id="reportButton" class="buttonDisabled" disabled>Nahlásit</button>
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
			<div class="footerOption"><a target='_blank' href='https://github.com/HonzaSTECH/Poznavacky/blob/master/TERMS_OF_SERVICE.md'>Podmínky služby</a></div>
			<div id="about" class="footerOption">&copy Štěchy a Eksyska, 2019</div>
         	<script>
             	function showLogin()
             	{
             		alert("Přihlašovací údaje pro nahlašování chyby:\nJméno: gjvj\nHeslo: poznavacky71");
             	}
         	</script>
         </footer>
  </body>
</html>
