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
				<div id="main_kod">
				    <?php
    				    //Zjistit, zda se již na tomto počítači někdo nedávno přihlašoval
    				    if(!isset($_COOKIE['lastChangelog']))
    				    {
    				        //Za poslední rok se nikdo nepřihlásil, nebo byly vymazány cookies
    				        //--> nechat zobrazený registrační formulář
    				        echo "<div id='registrace' style='display:block'>";
    				    }
    				    else
    				    {
    				        //Nedávno se tu někdo přihlašoval
    				        //--> skrýt registrační formulář
    				        echo "<div id='registrace' style='display:none'>";
    				    }
    				?>
    				    <span>Zaregistrujte se</span>
    				    <form method='post' action='register.php'>
    				    	<input type='text' name='name_input' maxlength=15 placeholder='Jméno' required=true class='text'>
    				    	<br>
    				    	<input type='password' name='pass_input' maxlength=31 placeholder='Heslo' required=true class='text'>
    				    	<br>
    				    	<input type='password' name='repass_input' maxlength=31 placeholder='Heslo znovu' required=true class='text'>
    				    	<br>
    				    	<input type='email' name='email_input' maxlength=255 placeholder='E-mail (nepovinné)' class='text'>
    				    	<br>
    				    	<span id='span_terms'>Registrací souhlasíte s <a target='_blank' href='https://github.com/HonzaSTECH/Poznavacky/blob/master/TERMS_OF_SERVICE.md'>podmínkami služby</a>.</span>
    				    	<br>
    				 		<input type=submit value='Vytvořit účet' class='confirm button'>
    				    </form>
    			        <span class='toggleForms'>Již máte účet? <a href="javascript:showLogin()">Přihlašte se</a>.</span>
    			        <?php
        			        //Chyby při minulé registraci
        			        $errors = @$_SESSION['registerErrors'];
        			        if (strlen($errors) > 0)
        			        {
        			            $errors = explode(';',$errors);
        				        echo "<ul class='errorList'>";
        				        foreach ($errors as $err)
        				        {
        				            echo "<li>".$err."</li>";
        				        }
        				        echo "</ul>";
        				        unset($_SESSION['registerErrors']);
        			        }
        			    ?>
    		        </div>
    		        
    		        <?php
    		        //Zjistit, zda se již na tomto počítači někdo nedávno přihlašoval
    		        if(isset($_COOKIE['lastChangelog']))
    		        {
    		            //Nedávno se tu někdo přihlašoval
    		            //--> nechat zobrazený přihlašovací formulář
    		            echo "<div id='prihlaseni' style='display:block'>";
    		        }
    		        else
    		        {
    		            //Za poslední rok se nikdo nepřihlásil, nebo byly vymazány cookies
    		            //--> skrýt přihlašovací formulář
    		            echo "<div id='prihlaseni' style='display:none'>";
    		        }
    			    ?>
    				    <span>Přihlašte se</span>
    				    <form method='post' action='login.php'>
    				    	<input type='text' name='name_input' maxlength=15 placeholder='Jméno' class='text'>
    				    	<br>
    				    	<input type='password' name='pass_input' maxlength=31 placeholder='Heslo' class='text'>
    				    	<br>
                            <label>
                                <input type='checkbox' name='stay_logged' class='big_checkbox'>
                                <span>Zůstat přihlášen</span>
                            </label>
    						<br>
    				 		<input type=submit value='Přihlásit se' class='confirm button'>
    			        </form>
    			        <span class='recoverPass'><a href="javascript:showPasswordRecovery()">Zapomněli jste heslo?</a></span>
    			        <br>
    			        <span class='toggleForms'>Ještě nemáte účet? <a href="javascript:showRegister()">Zaregistrujte se</a>.</span>
    			        <?php
        			        //Chyba při minulém přihlášení
        			        $error = @$_SESSION['loginError'];
        			        if (!empty($error))
        			        {
        			            echo "<ul class='errorList'>";
        			               echo "<li>".$error."</li>";
        			            echo "</ul>";
        			            unset($_SESSION['loginError']);
        			        }
    				    ?>
				    </div>
				    <div id="obnoveniHesla" style="display: none;">
				    	<span>Zadejte svojí e-mailovou adresu. Pokud existuje účet s takovou přidruženou adresou, pošleme na něj e-mail s instrukcemi k obnově hesla.</span>
                		<form method='post' action="recoverPassword.php">
                    		<input type=text name="email" maxlength=255 required=true />
                    		<input type=submit value="Odeslat" /> 
                		</form>
                		<span>Nepamatujete si, jakou jste zadávali při registraci e-mailovou adresu, nebo jste žádnou nezadávali? Napište nám na <i style="font-style: italic;">poznavacky@email.com</i> a my vám pomůžeme obnovit heslo jinou metodou.</span>
				    	<br>
				    	<a href="javascript:showLogin()">Zpět</a>
				    	<?php
    				    	//Chyba při minulém odeslání e-mailu
    				    	$error = @$_SESSION['passwordRecoveryError'];
    				    	if (!empty($error))
    				    	{
    				    	    echo "<ul class='errorList'>";
    				    	       echo "<li>".$error."</li>";
    				    	    echo "</ul>";
    				    	    unset($_SESSION['passwordRecoveryError']);
    				    	}
				        ?>
				    </div>
				</div>
			</main>
		</div>
		<footer id="cookiesAlert">
			<span>Tyto stránky využívají ke své funkci soubory cookie. Používáním stránek souhlasíte s ukládáním souborů cookie na vašem zařízení.</span>
			<div id="cookiesAlertCloser" onclick="hideCookies()">×</div>
		</footer>
	</body>
</html>