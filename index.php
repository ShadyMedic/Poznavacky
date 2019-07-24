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
				    <?php
    				    //Zjistit, zda se již na tomto počítači někdo nedávno přihlašoval
    				    
    				    if(!isset($_COOKIE['lastChangelog']))   //Za poslední rok se nikdo nepřihlásil, nebo byly vymazány cookies
    				    {
    				        echo "
                            <div id='registrace'>
        				    <span>Zaregistrujte se</span>
        				    <form method='post' action='register.php'>
        				    	<input type='text' name='name_input' maxlength=15 placeholder='Jméno' required=true class='text'>
        				    	<br>
        				    	<input type='password' name='pass_input' maxlength=31 placeholder='Heslo' required=true class='text'>
        				    	<br>
        				    	<input type='password' name='repass_input' maxlength=31 placeholder='Heslo znovu' required=true class='text'>
        				    	<br>
        				    	<input type='text' name='email_input' maxlength=255 placeholder='E-mail (nepovinné)' class='text'>
        				    	<br>
        				 		<input type=submit value='Vytvořit účet' class='confirm button'>
        				    </form>
        				    <span id='span_terms'>Registrací souhlasíte s <a target='_blank' href='https://github.com/HonzaSTECH/Poznavacky/blob/master/TERMS_OF_SERVICE.md'>podmínkami služby</a>.</span>
                            ";
    				        
    				        //Chyby při minulé registraci
    				        $errors = @$_SESSION['registerErrors'];
    				        $errors = explode(';',$errors);
    				        if (!empty($errors))
    				        {
        				        echo "<ul id='registerErrorList'>";
        				        foreach ($errors as $err)
        				        {
        				            echo "<li>".$err."</li>";
        				        }
        				        echo "</ul>";
    				        }
    				        
    				        echo "</div>";
    				    }
    				    else    //Zobrazit přihlašovací formulář
    				    {
    				        echo "
                            <div id='prihlaseni'>
            				    <span>Přihlašte se</span>
            				    <form method='post' onsubmit='login(event)'>
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