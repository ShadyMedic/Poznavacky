<?php    
    $redirectIn = false;
    $redirectOut = true;
    require 'verification.php';    //Obsahuje session_start();
    
    //Kontrola, zda je uživatel administrátorem.
    $username = $_SESSION['user']['name'];
    $query = "SELECT status FROM uzivatele WHERE jmeno='$username' LIMIT 1";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        header("Location: errSql.html");
        die();
    }
    $status = mysqli_fetch_array($result)['status'];
    if ($status !== 'admin')
    {
        //Zamítnutí přístupu
        header("Location: err403.html");
        die();
    }
    
    //Heslo raději znovu načtu z databáze - nebudu používat hash uložený v $_SESSION['user']['hash']
    $query = "SELECT heslo FROM uzivatele WHERE jmeno='$username' LIMIT 1";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        header("Location: errSql.html");
        die();
    }
    $adminHash = mysqli_fetch_array($result)['heslo'];
    //Kontrola zadaného hesla
    if (isset($_POST['adminPassword']) && !password_verify(@$_POST['adminPassword'], $adminHash))
    {
        echo "<span>Špatné heslo</span>";
    }
    if (!isset($_POST['adminPassword']) || !password_verify(@$_POST['adminPassword'], $adminHash))
    {
        echo "
            <form action='management.php' method=POST>
                <input type=password maxlength=31 placeholder='Zadejte administrátorské heslo' name='adminPassword'>
                <input type=submit value='OK'>
            </form>
        ";
    }
    
    if (password_verify(@$_POST['adminPassword'], $adminHash))
    {
        //Vymaž zadané heslo a počkej na vykreslení stránky
        unset($_POST['adminPassword']);
    }
    else
    {
        //Vymaž zadané heslo
        unset($_POST['adminPassword']);
        
        //Nevykresluj stránku - admin není autorizován
        die();
    }
?>

<!DOCTYPE html>
<html>
    <head>
    <meta charset="UTF-8">
	<script type="text/javascript" src="management.js"></script>
	<link rel="stylesheet" type="text/css" href="private.css">
    <title>Správa služby</title>
	</head>
    <body>
		<nav>
			<a onclick="firstTab()">Nastavení</a>
			<a onclick="secondTab()">Správa účtů</a>
			<a onclick="thirdTab()">Správa hlášení</a>
			<a onclick="fourthTab()">Správa změn jmen</a>
			<a onclick="fifthTab()">Poslat e-mail</a>
		</nav>
		<div id="container">
			<div id="tab1">
				Obsah 1
			</div>
			<div id="tab2">
				Obsah 2
			</div>
			<div id="tab3">
				Obsah 3
			</div>
			<div id="tab4">
				<table border=1>
					<tr>
    					<th>Současné jméno</th>
    					<th>Požadované jméno</th>
    					<th>Akce</th>
    				</tr>
					<?php
					   $query = "SELECT puvodni,nove FROM `zadostijmena` ORDER BY cas ASC LIMIT 25";
					   $result = mysqli_query($connection, $query);
					   if (!$result)
					   {
					       echo "Nastala chyba SQL: ".mysqli_error($connection);
					   }
					   while ($row = mysqli_fetch_array($result))
					   {
					        echo "<tr>";
					           echo "<td>";
					               echo $row['puvodni'];
					           echo "</td>";
					           echo "<td>";
					               echo $row['nove'];
					           echo "</td>";
					           echo "<td>";
					               echo "<button class='nameChangeAction activeBtn' onclick='acceptNameChange(event)'>";
					                   echo "<img src='tick.gif'/>";
                                   echo "</button>";
                                   echo "<button class='nameChangeAction activeBtn' onclick='declineNameChange(event)'>";
                                        echo "<img src='cross.gif'/>";
                                   echo "</button>";
                                   //Kontrola, jestli má uživatel zadaný e-mail
                                   $query = "SELECT email FROM uzivatele WHERE jmeno='".$row['puvodni']."' LIMIT 1";
                                   $email = mysqli_query($connection, $query);
                                   if (!$result)
                                   {
                                       echo "Nastala chyba SQL: ".mysqli_error($connection);
                                   }
                                   $email = mysqli_fetch_array($email)['email'];
                                   if (empty($email))
                                   {
                                       echo "<button class='nameChangeAction grayscale' onclick='sendMailNameChange(event)' disabled>";
                                   }
                                   else
                                   {
                                       echo "<button class='nameChangeAction activeBtn' onclick='sendMailNameChange(event)'>";
                                   }
                                        echo "<img src='mail.gif'/>";
                                   echo "</button>";
                               echo "</td>";
					        echo "</tr>";
					   }
					?>
				</table>
			</div>
			<div id="tab5">
				Obsah 5
			</div>
		</div>
	</body>
</html>