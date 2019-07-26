<?php 
    session_start();

    $userdata = $_SESSION['user'];
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<!-- <link rel="stylesheet" type="text/css" href="css.css"> -->
		<script type="text/javascript" src="accountSettings.js"></script>
		<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
		<title>Ověření</title>
		
		<style>
		  table{
		      border: 1px solid black;
		      position: absolute;
		      top: 0;
		      left: 0;
		      bottom: 0;
		      right: 0;
		      margin: auto;
		  }
		  .table_left{
		      text-align: left;
		  }
		  .table_right{
		      text-align: right;
		  }
		  .table_action{
		      text-align: center;
		  }
		  
		  #changeNameInput{
		      display: none;
		  }
		</style>
	</head>
	<body>
		<table id="static_info">
			<tr>
				<td class='table_left'>ID</td>
				<td class='table_right'><?php echo $userdata['id']; ?></td>
				<td class='table_action'><button disabled>Nelze změnit</button></td>
			</tr>
			<tr>
				<td class='table_left'>Jméno</td>
				<td class='table_right'><?php echo $userdata['name']; ?></td>
				<td class='table_action'>
					<button id="changeNameButton" onclick="changeName()">Vyžádat změnu</button>
					<div id="changeNameInput">
						<input id="changeNameInputField" type=text placeholder="Nové jméno" maxlength=15 />
						<button id="changeNameConfirm" onclick="confirmNameChange()">OK</button>
					</div>
				</td>
			</tr>
			<tr>
				<td class='table_left'>Heslo</td>
				<td class='table_right'>[Skryto]</td>
				<td class='table_action'><button onclick="changePassword()">Změnit</button></td>
			</tr>
			<tr>
				<td class='table_left'>E-mail</td>
				<td class='table_right'><?php echo $userdata['email']; ?></td>
				<td class='table_action'><button onclick="changeEmail()">Změnit</button></td>
			</tr>
			<tr>
				<td class='table_left'>Přidané obrázky</td>
				<td class='table_right'><?php echo $userdata['addedPics']; ?></td>
				<td class='table_action'>Pro zvýšení přidávejte obrázky</td>
			</tr>
			<tr>
				<td class='table_left'>Uhodnuté obrázky</td>
				<td class='table_right'><?php echo $userdata['guessedPics']; ?></td>
				<td class='table_action'>Pro zvýšení se nechejte testovat</td>
			</tr>
			<tr>
				<td class='table_left'>Karma</td>
				<td class='table_right'><?php echo $userdata['karma']; ?></td>
				<td class='table_action'>Karmu získáte za činost vedoucí ke zlepšení služby</td>
			</tr>
			<tr>
				<td class='table_left'>Status</td>
				<td class='table_right'><?php echo $userdata['status']; ?></td>
				<td class='table_action'>Zažádejte o status moderátora na poznavacky@email.com</td>
			</tr>
		</table>
	</body>
</html>