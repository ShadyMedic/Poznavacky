<?php
	$redirectIn = false;
	$redirectOut = true;
	require 'php/included/verification.php';    //Obsahuje session_start();
	require 'php/included/CONSTANTS.php';
	
	//Mazání zvolené poznávačky ze sezení
	unset($_SESSION['current']);
	
	//Nastavení cookie informující o skutečnosti, že se někdo nedávno přihlásil
	//To se využívá při načítání index.php a rozhoduje, zda se zobrazí registrační nebo přihlašovací formulář
	setcookie('recentLogin',1, time() + 60 * 60 * 24 * 365);
	
	$displayChangelog = false;
	if ($_SESSION['user']['lastChangelog'] < VERSION)
	{
	    $displayChangelog = true;
	    $_SESSION['user']['lastChangelog'] = VERSION;
	    $query = "UPDATE uzivatele SET posledni_changelog = ".VERSION." WHERE uzivatele_id = ".$_SESSION['user']['id'].";";
	    $result = mysqli_query($connection, $query);
	    if (!$result){header('Location: errSql.html');}
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width" />
		<link rel="stylesheet" type="text/css" href="css/css.css">
		<style>
		    <?php 
		        require 'php/included/themeHandler.php';
		    ?>
		</style>
		<script type="text/javascript" src="jScript/list.js"></script>
		<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
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
        <div id='listOverlay'></div> <!-- Zatemnění zbytku stránky -->
        <div id="changelogContainer">
        	<?php
        	if ($displayChangelog === true)
        	{
        	    echo "<script>document.getElementById('listOverlay').style.visibility = 'visible';</script>";
                echo "<div id='changelog'>"; //Okno se zprávou
				    echo "<div id='changelogText'>"; //Prvek se zprávou
					    include 'documents/changelog.html'; //Zpráva
				    echo "</div>";
				    echo "<div style='text-align:center'><button id='closeChangelog' class='button' onclick='closeChangelog()'>Zavřít</button></div>"; //Zavírací tlačítko
        	    echo "</div>";
        	}
        	?>
        </div>
        <div id="newClassFormContainer">
            <form onsubmit="applicationSubmit(event)">
                <h3>Žádost o založení nové třídy</h3>
                <?php
                    if (empty($_SESSION['user']['email']))
                    {
                        echo "
                        <span>Kontaktní e-mailová adresa</span>
                        <br>
                        <input id='newClassFormEmail' type=email, length=255 required />
                        <br>
                        ";
                    }
                ?>
                <span>Požadovaný název třídy</span><img src="images/info.png" title="Název nesmí kolidovat s jakoukoliv jinou třídou, která se později na stránkách může objevit. Proto vám doporučujeme zahrnout i název školy." style="width: 1rem; height: 1rem; padding-left: 0.5rem; padding-top: 0.75; display: inline;"/>
                <br>
                <input id='newClassFormName' type=text length=31 required />
                <br>
                <span>Kód třídy</span><img src="images/info.png" title="Třída bude po vytvoření nastavena jako soukromá. Pro přístup do třídy budou muset uživatelé nejprve zadat čtyřciferný vstupní kód. Tím se jim třída trvale odemkne. Kód si můžete později změnit v nastavení třídy nebo jej odebrat a udělat třídu veřejnou nebo jí naopak uzamknout pro nové uživatele úplně." style="width: 1rem; height: 1rem; padding-left: 0.5rem; padding-top: 0.75; display: inline;"/>
                <br>
                <input id='newClassFormCode' type=number min=0 max=9999 length=31 required />
                <br>
                <span>Je ještě něco, co bychom měli vědět?</span>
                <br>
                <textarea id='newClassFormInfo'></textarea>
                <br>
                <input type=submit value="Odeslat žádost" />
                <input type=button onclick="closeNewClassForm()" value="Zpět">
            </form>
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
                $userId = mysqli_real_escape_string($connection, $userId);
                $query = "SELECT posledni_uroven,posledni_slozka FROM uzivatele WHERE uzivatele_id=$userId LIMIT 1";
                $result = mysqli_query($connection, $query);
                $result = mysqli_fetch_array($result);
                $level = $result['posledni_uroven'];
                $folder = $result['posledni_slozka'];
                switch ($level)
                {
                    case 0:
                        include 'php/getClasses.php';
                        break;
                    case 1:
                        $_GET['classId'] = $folder;
                        include 'php/getGroups.php';
                        break;
                    case 2:
                        $_GET['groupId'] = $folder;
                        include 'php/getParts.php';
                        echo "<script>setSolidDimensions();</script>";
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
