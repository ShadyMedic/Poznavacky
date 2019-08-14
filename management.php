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
			<a onclick="sixthTab()">Ovládání databáze</a>
		</nav>
		<div id="container">
			<div id="tab1">
				<div id='constants'>
    				<h3>Správa systémových konstant</h3>
    				<?php 
    				    $constantsFile = file('CONSTANTS.php');
    				    echo "<table id='constantsTable'>";
    				    foreach ($constantsFile as $fileLine)
    				    {
    				        if (strpos($fileLine, 'define') === 0)  //Řádka obsahuje definici konstanty
    				        {
    				            //Získat jméno a hodnotu konstanty
    				            $fileLine = str_replace('define(\'', '', $fileLine);
    				            $fileLine = str_replace(');', '', $fileLine);
    				            
    				            $pos = strpos($fileLine, '\'');
    				            $constantName = '';
    				            for ($i = 0; $i < $pos; $i++)
    				            {
    				                $constantName .= $fileLine[0];
    				                $fileLine = substr($fileLine, 1);   //Odstranění přepsaného znaku
    				            }
    				            
    				            $fileLine = str_replace('\', ', '', $fileLine);
    				            
    				            if (strpos($fileLine, '\'') === 0) //Hodnota je řetězec --> odstraníme apostrofy
    				            {
    				                $fileLine = str_replace('\'', '', $fileLine);
    				            }
    				            
    				            $constantValue = $fileLine;
    				            
    				            //Vypsat jméno a hodnotu konstanty
    				            echo "<tr>";
    				                echo "<td>";
    				                    echo $constantName;
    				                echo "</td>";
    				                echo "<td>";
    				                    echo "<input type=text readonly value=$constantValue />";
    				                echo "</td>";
    				                echo "<td>";
    				                    echo "<button class='activeBtn' onclick='editConstant(event)' title='Upravit konstantu'><img src='pencil.gif'></button>";
    				                    echo "<button class='activeBtn' onclick='moveConstantUp(event)' title='Posunout nahoru'><img src='up.gif'></button>";
    				                    echo "<button class='activeBtn' onclick='moveConstantDown(event)' title='Posunout dolů'><img src='down.gif'></button>";
    				                    echo "<button class='activeBtn' onclick='deleteConstant(event)' title='Odstranit konstantu'><img src='cross.gif'></button>";
    				                echo "</td>";
    				            echo "</tr>";
    				        }
    				    }
    				    echo "</table>";
    			    ?>
    			    <div style="text-align: center;">
        				<button class='actionButton activeBtn centerBtn' onclick='addConstant()' title='Přidat novou konstantu'><img src='plus.gif'></button>
        				<br>
        				<button class='centerBtn' onclick='saveConstants()' title='Přidat novou konstantu'>Uložit konstanty</button>
    				</div>
    			</div>
			</div>
			<div id="tab2">
				<table border=1>
					<tr>
    					<th>ID</th>
    					<th>Jméno</th>
    					<th>E-mail</th>
    					<th>Poslední přihlášení</th>
    					<th>Přidané obrázky</th>
    					<th>Uhodnuté obrázky</th>
    					<th>Karma</th>
    					<th>Status</th>
    					<th>Akce</th>
    				</tr>
					<?php
					   $query = "SELECT id,jmeno,email,posledniPrihlaseni,pridaneObrazky,uhodnuteObrazky,karma,status FROM uzivatele ORDER BY posledniPrihlaseni DESC LIMIT 25";
					   $result = mysqli_query($connection, $query);
					   if (!$result)
					   {
					       echo "Nastala chyba SQL: ".mysqli_error($connection);
					   }
					   while ($row = mysqli_fetch_array($result))
					   {
					        echo "<tr>";
					           echo "<td>";
					               echo $row['id'];
					           echo "</td>";
					           echo "<td>";
					               echo $row['jmeno'];
					           echo "</td>";
					           echo "<td>";
					               echo $row['email'];
					           echo "</td>";
					           echo "<td>";
					               echo $row['posledniPrihlaseni'];
					           echo "</td>";
					           echo "<td>";
					               echo "<input type=number readonly value=".$row['pridaneObrazky']." class='userField'/>";
					           echo "</td>";
					           echo "<td>";
					               echo "<input type=number readonly value=".$row['uhodnuteObrazky']." class='userField'/>";
					           echo "</td>";
					           echo "<td>";
					               echo "<input type=number readonly value=".$row['karma']." class='userField'/>";
					           echo "</td>";
					           echo "<td>";
					               echo "<select disabled class='userField'>";
					                   echo "<option";     if ($row['status'] === "admin"){echo " selected";}   echo ">admin</option>";
					                   echo "<option";     if ($row['status'] === "moderator"){echo " selected";}   echo ">moderator</option>";
					                   echo "<option";     if ($row['status'] === "member"){echo " selected";}   echo">member</option>";
					                   echo "<option";     if ($row['status'] === "guest"){echo " selected";}   echo">guest</option>";
					               echo "</select>";
					           echo "</td>";
					           echo "<td>";
					               if ($row['id'] !== $_SESSION['user']['id']) //U přihlášeného administrátora nezobrazuj akce
					               {
    					               echo "<button class='userAction activeBtn editButton' onclick='editUser(event)' title='Upravit'>";
    					                   echo "<img src='pencil.gif'/>";
                                       echo "</button>";
                                       //Kontrola, jestli má uživatel zadaný e-mail
                                       $query = "SELECT email FROM uzivatele WHERE jmeno='".$row['jmeno']."' LIMIT 1";
                                       $email = mysqli_query($connection, $query);
                                       if (!$result)
                                       {
                                           echo "Nastala chyba SQL: ".mysqli_error($connection);
                                       }
                                       $email = mysqli_fetch_array($email)['email'];
                                       if (empty($email))
                                       {
                                           echo "<button class='userAction grayscale' disabled>";
                                       }
                                       else
                                       {
                                           echo "<button class='userAction activeBtn' onclick='sendMailNameChange(\"$email\")' title='Poslat e-mail'>";
                                       }
                                       echo "<img src='mail.gif'/>";
                                       echo "</button>";
                                       echo "<button class='userAction activeBtn' onclick='deleteUser(event)' title='Odstranit'>";
                                            echo "<img src='cross.gif'/>";
                                       echo "</button>";
					               }
                               echo "</td>";
					        echo "</tr>";
					   }
					?>
				</table>
			</div>
			<div id="tab3">
				<!-- TODO -->
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
					   $query = "SELECT puvodni,nove FROM zadostijmena ORDER BY cas ASC LIMIT 25";
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
					               echo "<button class='nameChangeAction activeBtn' onclick='acceptNameChange(event)' title='Přijmout'>";
					                   echo "<img src='tick.gif'/>";
                                   echo "</button>";
                                   echo "<button class='nameChangeAction activeBtn' onclick='declineNameChange(event)' title='Zamítnout'>";
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
                                       echo "<button class='nameChangeAction grayscale' disabled>";
                                   }
                                   else
                                   {
                                       echo "<button class='nameChangeAction activeBtn' onclick='sendMailNameChange(\"$email\")' title='Poslat e-mail'>";
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
				<div id="email">
					<span>Adresát: </span>
					<input id="emailAddressee" type=email maxlength=255 />
					<br>
					<span>Předmět: </span>
					<input id="emailSubject" type=text maxlength=70 />
					<br>
					<textarea id="emailMessage" rows="20" cols="70" placeholder="Zpráva"></textarea>
					<br>
					<button id="emailPreviewButton" onclick="updateEmailPreview()">Zobrazit náhled</button>
					<button id="emailSendButton" onclick="sendMail()">Odeslat</button>
					<div id="emailPreview">
					Náhled e-mailu se zobrazí zde
					</div>
				</div>
			</div>
			<div id="tab6">
				<div id="sql">
					<div id="sqlWarning">
						<span>Neuvědomělé používání tohoto nástroje může mít destruktivní účinky. Používejte tento nástroj pouze v případě, že jste si naprosto jistí tím, co děláte!</span>
					</div>
					<div id="sqlQuery">
						<textarea id="sqlQueryInput" rows=5 cols=150 placeholder="Zadejte příkazy. Každý příkaz musí být ukončen středníkem (;)"></textarea>
						<br>
						<button onclick="sendSqlQuery()">Odeslat</button>
					</div>
					<div id="sqlResult">
						Žádný výstup
					</div>
				</div>
			</div>
		</div>
	</body>
</html>