<?php
	$redirectIn = false;
	$redirectOut = true;
	require 'php/included/verification.php';    //Obsahuje session_start();
	require 'php/included/CONSTANTS.php';
	
	//Mazání zvolené poznávačky ze sezení
	unset($_SESSION['current']);
	
	$displayChangelog = false;
	if (!(isset($_COOKIE['lastChangelog']) && $_COOKIE['lastChangelog'] == VERSION))
    {
		setcookie('lastChangelog',VERSION, time() + 60 * 60 * 24 * 365);
		$displayChangelog = true;
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width" />
		<link rel="stylesheet" type="text/css" href="css/css.css">
		<script type="text/javascript" src="jScript/list.js"></script>
		<link rel="icon" href="images/favicon.ico">
		<link rel="apple-touch-icon" sizes="180x180" href="images/apple-touch-icon.png">
		<link rel="icon" type="image/png" sizes="32x32" href="images/icon-32x32.png">
		<link rel="icon" type="image/png" sizes="16x16" href="images/icon-16x16.png">
		<link rel="manifest" href="manifest.json">
		<link rel="mask-icon" href="images/safari-pinned-tab.svg" color="#ffc835">
		<meta name="theme-color" content="#ffffff">
		<title>Poznávačky</title>
	</head>
	<body>
    <div class="container">
        <div id="changelogContainer">
        	<?php
        	if ($displayChangelog === true)
        	{
        	    echo "<div id='changelogOverlay'></div>"; //Zatemnění zbytku stránky
				
        	    echo "<div id='changelog'>"; //Okno se zprávou
					echo "<div id='changelogText'>"; //Prvek se zprávou
						include 'documents/changelog.html'; //Zpráva
					echo "</div>";
					echo "<div style='text-align:center'><button id='closeChangelog' class='button' onclick='closeChangelog()'>Zavřít</button></div>"; //Zavírací tlačítko
        	    echo "</div>";
        	}
        	?>
        </div>
        <header>
			<h1>Dostupné poznávačky</h1>
			<nav>
				<?php
				    if ($_SESSION['user']['status'] === 'admin')
				    {
					echo "<a href='management.php'>Správa služby</a>";
				    }
				?>
				<a href="accountSettings.php">Nastavení účtu</a>
				<a href="php/logout.php">Odhlásit se</a>
			</nav>
        </header>
        <main id="table">
            <?php
                $userId = $_SESSION['user']['id'];
                mysqli_real_escape_string($connection, $query);
                $query = "SELECT posledniUroven,posledniSlozka FROM uzivatele WHERE id=$userId LIMIT 1";
                $result = mysqli_query($connection, $query);
                $result = mysqli_fetch_array($result);
                $level = $result['posledniUroven'];
                $folder = $result['posledniSlozka'];
                
                switch ($level)
                {
                    case 0:
                        include 'php/ajax/getClasses.php';
                        break;
                    case 1:
                        $_GET['classId'] = $folder;
                        include 'php/ajax/getGroups.php';
                        break;
                    case 2:
                        $_GET['groupId'] = $folder;
                        include 'php/ajax/getParts.php';
                        break;
                }
            ?> 
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
</html>
