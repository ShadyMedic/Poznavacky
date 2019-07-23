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
		<div class="container">
			<main>
				<div id="main_kod">
					<!--
					<span id="span_kod">Zadejte ověřovací kód:</span><br>
					<form onsubmit="validate(event)">
						<input type=text maxlength=8 class="text" id="kod_input">
						<br>
						<input type=submit value="Potvrdit" class="confirm button">
					</form>
					<span id="span_terms">Odesláním kódu souhlasíte s <a target="_blank" href="https://github.com/HonzaSTECH/Poznavacky/blob/master/TERMS_OF_SERVICE.md">podmínkami služby</a>.</span>
				    -->
				    <?php
    				    //Zjistit, zda se již na tomto počítači někdo nedávno přihlašoval
    				    
    				    if(!isset($_COOKIE['lastChangelog']))   //Za poslední rok se nikdo nepřihlásil, nebo byly vymazány cookies
    				    {
    				        echo "
                            <div id='registrace'>
        				    <span>Zaregistrujte se</span>
        				    <form onsubmit='register(event)'>
        				    	<input type='text' name='name_input' maxlength=15 placeholder='Jméno' class='text'>
        				    	<br>
        				    	<input type='text' name='pass_input' maxlength=31 placeholder='Heslo' class='text'>
        				    	<br>
        				    	<input type='text' name='repass_input' maxlength=31 placeholder='Heslo znovu' class='text'>
        				    	<br>
        				    	<input type='text' name='email_input' maxlength=255 placeholder='E-mail (nepovinné)' class='text'>
        				    	<br>
        				 		<input type=submit value='Vytvořit účet' class='confirm button'>
        				    </form>
        				    <span id='span_terms'>Registrací souhlasíte s <a target='_blank' href='https://github.com/HonzaSTECH/Poznavacky/blob/master/TERMS_OF_SERVICE.md'>podmínkami služby</a>.</span>
                        </div>
                            ";
    				    }
    				    else    //Zobrazit přihlašovací formulář
    				    {
    				        echo "
                            <div id='prihlaseni'>
            				    <span>Přihlašte se</span>
            				    <form onsubmit='login(event)'>
            				    	<input type='text' name='name_input' maxlength=15 placeholder='Jméno' class='text'>
            				    	<br>
            				    	<input type='text' name='pass_input' maxlength=31 placeholder='Heslo' class='text'>
            				    	<br>
            				 		<input type=submit value='Přihlásit se' class='confirm button'>
        				        </form>
    					   </div>
                            ";
    				    }
				    ?>
				</div>
			</main>
		</div>
		<footer id="cookiesAlert">
			<span>Tyto stránky využívají ke své funkci soubory cookie. Používáním stránek souhlasíte s ukládáním souborů cookie na vašem zařízení.</span>
			<div id="cookiesAlertCloser" onclick="hideCookies()">×</div>
		</footer>
	</body>
</html>