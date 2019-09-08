 <?php
	$redirectIn = false;
	$redirectOut = true;
	require 'verification.php';    //Obsahuje session_start();
	require 'CONSTANTS.php';
	
	//Mazání zvolené poznávačky ze sezení
	unset($_SESSION['current']);
	
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" type="text/css" href="css.css">
		<script type="text/javascript" src="list.js"></script>
		<title>Poznávačky</title>
	</head>
	<body>
    <div class="container">
        <div id="changelogContainer">
        	<?php
        	if (!(isset($_COOKIE['lastChangelog']) && $_COOKIE['lastChangelog'] == VERSION))
        	{
        	    setcookie('lastChangelog',VERSION, time() + 60 * 60 * 24 * 365);
				
        	    echo "<div id='changelogOverlay'></div>"; //Zatemnění zbytku stránky
				
        	    echo "<div id='changelog'>"; //Okno se zprávou
					echo "<div id='changelogText'>"; //Prvek se zprávou
						include 'changelog.html'; //Zpráva
					echo "</div>";
					echo "<div style='text-align:center'><button id='closeChangelog' class='button' onclick='closeChangelog()'>Zavřít</button></div>"; //Zavírací tlačítko
        	    echo "</div>";
        	}
        	?>
        </div>
        <header>
            <h1>Dostupné poznávačky</h1>
            <a href="logout.php" style="position: absolute; right: 0; margin: auto;">Odhlásit se</a>
            <a href="accountSettings.php" style="position: absolute; right: 100px; margin: auto;">Nastavení účtu</a>
        </header>
        <main>
            <table id="listTable">
		 	    <tr>
    		 		<th>Název</th>
    		 		<th>Přírodniny</th>
    		 		<th>Obrázky</th>
    		 	</tr>
    		 	<?php
    				//Seznam dostupných poznávaček
    				include 'connect.php';
    				
    				$query = 'SELECT * FROM poznavacky';
    				$result = mysqli_query($connection,$query);
    				while ($info = mysqli_fetch_array($result))
    				{
    					echo '<tr class="listRow" onclick="choose(\''.$info['id'].'&'.$info['nazev'].'\')">';
    						echo '<td class="listNames">'.$info['nazev'].'</td>';
    						echo '<td class="listNaturals">'.$info['prirodniny'].'</td>';
    						echo '<td class="listPics">'.$info['obrazky'].'</td>';
    					echo '</tr>';
    				}
    			?> 
            </table>
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
</html>