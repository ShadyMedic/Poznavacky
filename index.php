<?php
	$redirectIn = true;
	$redirectOut = false;
	include 'verification.php';    //Obsahuje session_start();
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
				<?php
    			//Zjistit, zda se již na tomto počítači někdo nedávno přihlašoval, nebo zda existují chyby registrace k zobrazení
			    if (isset($_SESSION['registerErrors']) || (!isset($_COOKIE['lastChangelog'])) && !isset($_SESSION['loginError']) && !isset($_SESSION['passwordRecoveryError']))
					{
						//Podmínka splněna --> nechat zobrazený registrační formulář
						echo "<div id='registrace' style='display:block'>";    				    }
					else
					{
						//Podmínka nesplněna --> skrýt registrační formulář
						echo "<div id='registrace' style='display:none'>";
					}
				?>
					<h2>Zaregistrujte se</h2>
					<div class="udaje">
						<input id='register_name' type='text' name='name_input' maxlength=15 placeholder='Jméno' required=true class='text'>
						<br>
						<input id='register_pass' type='password' name='pass_input' maxlength=31 placeholder='Heslo' required=true class='text'>
						<br>    				    	
						<input id='register_repass' type='password' name='repass_input' maxlength=31 placeholder='Heslo znovu' required=true class='text'>
						<br>
						<input id='register_email' type='email' name='email_input' maxlength=255 placeholder='E-mail (nepovinné)' class='text'>
						<br>
						<span id='span_terms'>Registrací souhlasíte s <a target='_blank' href='https://github.com/HonzaSTECH/Poznavacky/blob/master/TERMS_OF_SERVICE.md'>podmínkami služby</a>.</span>
						<br>
						<button onclick='register()' class='button' class='confirm button'>Vytvořit účet</button>
					</div>
					<span class='toggleForms'>Již máte účet? <a href="javascript:showLogin()">Přihlašte se</a>.</span>
					<div id='registerErrors'>
					</div>
				</div>
    		
				<?php
					//Zjistit, zda se již na tomto počítači někdo nedávno přihlašoval
					if (isset($_COOKIE['lastChangelog']))
					{
						//Podmínka splněna --> nechat zobrazený přihlašovací formulář
						echo "<div id='prihlaseni' style='display:block'>";
					}
					else
					{
						//Podmínka nesplněna --> skrýt přihlašovací formulář
						echo "<div id='prihlaseni' style='display:none'>";
					}
				?>
					<h2>Přihlašte se</h2>
					<div class="udaje">
						<input id='login_name' type='text' name='name_input' maxlength=15 placeholder='Jméno' class='text'>
						<br>
						<input id='login_pass' type='password' name='pass_input' maxlength=31 placeholder='Heslo' class='text'>
						<br>
						<div class="checkbox">
							<input type="checkbox" id="login_keep" name='stay_logged'/>
							<label for="login_keep">Zůstat přihlášen</label>
						</div>
						<br>
						<button onclick='login()' class='button' class='confirm button'>Přihlásit se</button>
					</div>
					<span class='recoverPass'><a href="javascript:showPasswordRecovery()">Zapomněli jste heslo?</a></span>
					<br>
					<span class='toggleForms'>Ještě nemáte účet? <a href="javascript:showRegister()">Zaregistrujte se</a>.</span>
					<div id='loginErrors'>
					</div>
				</div>

				<div id="obnoveniHesla" style="display: none;">
					<span>Zadejte svojí e-mailovou adresu. Pokud existuje účet s takovou přidruženou adresou, pošleme na něj e-mail s instrukcemi k obnově hesla.</span>
					<div>
						<input class="text" id='passRecovery_input' type=text name="email" maxlength=255 required=true />
						<button onclick="recoverPassword()" class="button">Odeslat</button> 
					</div>
					<span>Nepamatujete si, jakou jste zadávali při registraci e-mailovou adresu, nebo jste žádnou nezadávali? Napište nám na <i style="font-style: italic;">poznavacky@email.com</i> a my vám pomůžeme obnovit heslo jinou metodou.</span>
					<br>
					<button class="button"onclick="showLogin()">Zpět</button>
					<div id='passwordRecoveryErrors'>
					</div>
				</div>
			</main>
		</div>
		<footer id="cookiesAlert">
			<div>Tyto stránky využívají ke své funkci soubory cookie. Používáním stránek souhlasíte s ukládáním souborů cookie na vašem zařízení.</div>
			<div id="cookiesAlertCloser" onclick="hideCookies()">x</div>
		</footer>
	</body>
</html>
