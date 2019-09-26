<?php 
    $redirectIn = false;
    $redirectOut = true;
    require 'verification.php';    //Obsahuje session_start();

    $userdata = $_SESSION['user'];
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width" />
		<link rel="stylesheet" type="text/css" href="css.css">
		<script type="text/javascript" src="accountSettings.js"></script>
		<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
		<title>Správa účtu</title>
		<style>
			#changeNameInput, #changePasswordInput1, #changePasswordInput2, #changePasswordInput3, #changeEmailInput1, #changeEmailInput2, #deleteAccountInput1, #deleteAccountInput2 {
    			display: none;
				}
		</style>
	</head>
	<body>
	<div class="container">
		<header>
            <h1>Správa účtu</h1>
        </header>
        <main class="basic_main">
			<table id="static_info">
				<tr>
					<td class='table_left'>ID</td>
					<td class='table_right'><?php echo $userdata['id']; ?></td>
					<td class='table_action'><!--<button disabled class="buttonDisabled">Nelze změnit</button>--></td>
				</tr>	
				<tr>
					<td class='table_left'>Jméno</td>
					<td class='table_right' id="username"><?php echo $userdata['name']; ?></td>
					<td class='table_action'>
						<button class="button" id="changeNameButton" onclick="changeName()">Vyžádat změnu</button>
						<div id="changeNameInput">
							<input class="text" id="changeNameInputField" type=text placeholder="Nové jméno" maxlength=15 />
							<button class="button" id="changeNameConfirm" onclick="confirmNameChange()">Potvrdit</button>
						</div>
					</td>
				</tr>
				<tr>
					<td class='table_left'>Heslo</td>
					<td class='table_right'>[Skryto]</td>
					<td class='table_action'>
						<button class="button" id="changePasswordButton" onclick="changePassword()">Změnit</button>
						<div id="changePasswordInput1">
							<input class="text" id="changePasswordInputFieldOld" type=password placeholder="Staré heslo" maxlength=31 />
							<button class="button" id="changePasswordNext1" onclick="changePasswordVerify()">Dále</button>
						</div>
						<div id="changePasswordInput2">
							<input class="text" id="changePasswordInputFieldNew" type=password placeholder="Nové heslo" maxlength=31 />
							<button class="button" id="changePasswordNext2" onclick="changePasswordStage3()">Dále</button>
							<button class="button" id="changePasswordBack2" onclick="changePassword()">Zpět</button>
						</div>
						<div id="changePasswordInput3">
							<input class="text" id="changePasswordInputFieldReNew" type=password placeholder="Nové heslo znovu" maxlength=31 />
							<button class="button" id="changePasswordConfirm" onclick="confirmPasswordChange()">Potvrdit</button>
							<button class="button" id="changePasswordBack3" onclick="changePasswordStage2()">Zpět</button>
						</div>
					</td>
				</tr>
				<tr>
					<td class='table_left'>E-mail</td>
					<td class='table_right' id="emailAddress"><?php echo $userdata['email']; ?></td>
					<td class='table_action'>
						<button class="button" id="changeEmailButton" onclick="changeEmail()">Změnit</button>
						<div id="changeEmailInput1">
							<input class="text" id="changeEmailPasswordInputField" type=password placeholder="Heslo pro ověření" maxlength=31 />
							<button class="button" id="changeEmailNext" onclick="changeEmailVerify()">Dále</button>
						</div>
						<div id="changeEmailInput2">
							<input class="text" id="changeEmailInputField" type=text placeholder="Nový e-mail" maxlength=255 />
							<button class="button" id="changeEmailConfirm" onclick="confirmEmailChange()">Potvrdit</button>
							<button class="button" id="changeEmailBack" onclick="changeEmail()">Zpět</button>
						</div>
					</td>
				</tr>
				<tr>
					<td class='table_left' title="Pro zvýšení přidávejte obrázky">Přidané obrázky</td>
					<td class='table_right'><?php echo $userdata['addedPics']; ?></td>
					<!--<td class='table_action'>Pro zvýšení přidávejte obrázky</td>-->
					<td class='table_action'></td>
				</tr>
				<tr>
					<td class='table_left' title="Pro zvýšení se nechejte testovat">Uhodnuté obrázky</td>
					<td class='table_right'><?php echo $userdata['guessedPics']; ?></td>
					<!--<td class='table_action'>Pro zvýšení se nechejte testovat</td>-->
					<td class='table_action'></td>
				</tr>
				<tr>
					<td class='table_left' title="Karmu získáte za činnost vedoucí ke zlepšení služby">Karma</td>
					<td class='table_right'><?php echo $userdata['karma']; ?></td>
					<!--<td class='table_action'>Karmu získáte za činnost vedoucí ke zlepšení služby</td>-->
					<td class='table_action'></td>
				</tr>
				<tr id="tr_end">
					<td class='table_left'>Status</td>
					<td class='table_right'><?php echo $userdata['status']; ?></td>
					<!--<td class='table_action'>Zažádejte o status moderátora na poznavacky@email.com</td>-->
					<td class='table_action'></td>
				</tr>
			</table>
			
			<button class="button" id="deleteAccountButton" onclick="deleteAccount()">Odstranit účet</button>
			<div id="deleteAccountInput1">
				<input class="text" id="deleteAccountInputField" type=password placeholder="Zadejte své heslo" maxlength=31 />
				<button class="button" id="deleteAccountConfirm" onclick="deleteAccountVerify()">OK</button>
			</div>
			<div id="deleteAccountInput2">
				<span>Tato akce je nevratná. Opravdu si přejete trvale odstranit svůj účet?</span><br>
				<button class="button" id="deleteAccountFinalConfirm" onclick="deleteAccountFinal()">Ano, odstranit účet</button>
				<button class="button" id="deleteAccountFinalCancel" onclick="deleteAccountCancel()">Ne, zrušit odstranění účtu</button>
			</div>
			<br>
			
			<a href="list.php"><button class="button">Zpět</button></a>
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