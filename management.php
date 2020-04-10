<?php    
    $redirectIn = false;
    $redirectOut = true;
    require 'php/included/verification.php';    //Obsahuje session_start();
    
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
	<script type="text/javascript" src="jScript/management.js"></script>
	<link rel="stylesheet" type="text/css" href="css/private.css">
    <title>Správa služby</title>
	</head>
    <body>
	<div id="container">
		<header>
		<nav>
			<a id="tab1link" onclick="firstTab()" style="background-color: #9999FF;">Nastavení</a>
			<a id="tab2link" onclick="secondTab()">Správa účtů</a>
			<a id="tab3link" onclick="thirdTab()">Správa hlášení</a>
			<a id="tab4link" onclick="fourthTab()">Správa změn jmen</a>
			<a id="tab5link" onclick="fifthTab()">Poslat e-mail</a>
			<a id="tab6link" onclick="sixthTab()">Ovládání databáze</a>
			<a href="list.php" style="background-color: #FFFF99">Návrat</a>
		</nav>
		</header>
		<main>
			<div id="tab1">
				<div id='constants'>
    				<h3>Správa systémových konstant</h3>
    				<?php 
    				    $constantsFile = file('php/included/CONSTANTS.php');
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
    				                    echo "<button class='editConstantButton activeBtn icon-btn' onclick='editConstant(event)' title='Upravit konstantu'><img src='images/pencil.svg'></button>";
    				                    echo "<button class='moveUpButton activeBtn' onclick='moveConstantUp(event)' title='Posunout nahoru'><img src='images/up.svg'></button>";
    				                    echo "<button class='moveDownButton activeBtn icon-btn' onclick='moveConstantDown(event)' title='Posunout dolů'><img src='images/down.svg'></button>";
    				                    echo "<button class='activeBtn' onclick='deleteConstant(event)' title='Odstranit konstantu'><img src='images/cross.svg'></button>";
    				                echo "</td>";
    				            echo "</tr>";
    				        }
    				    }
    				    echo "</table>";
    			    ?>
    			    <!-- Vypnutí prvního a posledního tlačítka posunu, které bylo právě vykresleno -->
    			    <script>
						reevaluateMoveButtons();
    			    </script>
    			    
        			<button class='actionButton activeBtn centerBtn' onclick='addConstant()' title='Přidat novou konstantu'><img src='images/plus.svg'></button>
        			<br>
        			<button class='centerBtn borderBtn' onclick='saveConstants()' title='Přidat novou konstantu'>Uložit konstanty</button>
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
					   $query = "SELECT uzivatele_id,jmeno,email,posledni_prihlaseni,pridane_obrazky,uhodnute_obrazky,karma,status FROM uzivatele ORDER BY posledni_prihlaseni DESC LIMIT 25";
					   $result = mysqli_query($connection, $query);
					   if (!$result)
					   {
					       echo "Nastala chyba SQL: ".mysqli_error($connection);
					   }
					   while ($row = mysqli_fetch_array($result))
					   {
					        echo "<tr>";
					           echo "<td>";
					               echo $row['uzivatele_id'];
					           echo "</td>";
					           echo "<td>";
					               echo $row['jmeno'];
					           echo "</td>";
					           echo "<td>";
					               echo $row['email'];
					           echo "</td>";
					           echo "<td>";
					               echo $row['posledni_prihlaseni'];
					           echo "</td>";
					           echo "<td>";
					               echo "<input type=number readonly value=".$row['pridane_obrazky']." class='userField'/>";
					           echo "</td>";
					           echo "<td>";
					               echo "<input type=number readonly value=".$row['uhodnute_obrazky']." class='userField'/>";
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
					               if ($row['uzivatele_id'] !== $_SESSION['user']['id']) //U přihlášeného administrátora nezobrazuj akce
					               {
    					               echo "<button class='userAction activeBtn editButton' onclick='editUser(event)' title='Upravit'>";
    					                   echo "<img src='images/pencil.svg'/>";
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
                                       echo "<img src='images/mail.svg'/>";
                                       echo "</button>";
                                       echo "<button class='userAction activeBtn' onclick='deleteUser(event)' title='Odstranit'>";
                                            echo "<img src='images/cross.svg'/>";
                                       echo "</button>";
					               }
                               echo "</td>";
					        echo "</tr>";
					   }
					?>
				</table>
			</div>
			<div id="tab3">
					<?php 
					    include 'php/included/getReports.php';
                    ?>
				<div id="singleReport"></div>
			</div>
			<div id="tab4">
				<table border=1>
					<tr>
    					<th>Současné jméno</th>
    					<th>Požadované jméno</th>
    					<th>Akce</th>
    				</tr>
					<?php
					   $query = "SELECT uzivatele_jmeno,nove FROM zadosti_jmena ORDER BY cas ASC LIMIT 25";
					   $result = mysqli_query($connection, $query);
					   if (!$result)
					   {
					       echo "Nastala chyba SQL: ".mysqli_error($connection);
					   }
					   while ($row = mysqli_fetch_array($result))
					   {
					        echo "<tr>";
					           echo "<td>";
					               echo $row['uzivatele_jmeno'];
					           echo "</td>";
					           echo "<td>";
					               echo $row['nove'];
					           echo "</td>";
					           echo "<td>";
					               echo "<button class='nameChangeAction activeBtn' onclick='acceptNameChange(event)' title='Přijmout'>";
					                   echo "<img src='images/tick.svg'/>";
                                   echo "</button>";
                                   echo "<button class='nameChangeAction activeBtn' onclick='declineNameChange(event)' title='Zamítnout'>";
                                        echo "<img src='images/cross.svg'/>";
                                   echo "</button>";
                                   //Kontrola, jestli má uživatel zadaný e-mail
                                   $query = "SELECT email FROM uzivatele WHERE jmeno='".$row['uzivatele_jmeno']."' LIMIT 1";
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
                                        echo "<img src='images/mail.svg'/>";
                                   echo "</button>";
                               echo "</td>";
					        echo "</tr>";
					   }
					?>
				</table>
			</div>
			<div id="tab5">
				<div id="email">
					<table>
						<tr>
							<td><span>Adresát:</span></td>
							<td><input id="emailAddressee" type=email maxlength=255 /></td>
						</tr>
						<tr>
							<td><span>Předmět:</span></td>
							<td><input id="emailSubject" type=text maxlength=70 /></td>
						</tr>
					</table>
					<textarea id="emailMessage" rows="20" cols="70" placeholder="Zpráva"></textarea>
					<br>
					<span>Bezpečnostní kód: </span>
					<input id="emailCode" type=text maxlength=8 value="<?php include 'php/included/CONSTANTS.php'; echo EMAIL_CODE;?>" />
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
		</main>
	</div>
	</body>
</html>
