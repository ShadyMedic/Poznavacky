<?php
  $redirectIn = false;
  $redirectOut = true;
  require 'php/included/verification.php';    //Obsahuje session_start();
  
  //Nastavování současné části
  $pId = @$_COOKIE['current'];
  
  $everything = false;
  
  //Zjištění, zda se nejedná o výběr všech částí v dané poznávačce
  if (strpos($pId,','))
  {
    //Zjištění jmena poznávačky
    include 'php/included/connect.php';
    $pId = mysqli_real_escape_string($connection, $pId);
    if (!empty($pId))
    {
      $everything = true;
      $firstPartId = $pId[0];
      $query = "SELECT poznavacky_id,nazev FROM poznavacky WHERE poznavacky_id=(SELECT poznavacky_id FROM casti WHERE casti_id=$firstPartId LIMIT 1) LIMIT 1";
      $result = mysqli_query($connection, $query);
      if (!$result){echo mysqli_error($connection);}
      $result = mysqli_fetch_array($result);
      $pName = $result['nazev'].' - Vše';
      $pId = $result['poznavacky_id'];
  	}
  }
  else
  {
    //Zjištění jmena části
    include 'php/included/connect.php';
    $pId = mysqli_real_escape_string($connection, $pId);
    if (!empty($pId))
    {
      $query = "SELECT nazev FROM casti WHERE casti_id=$pId LIMIT 1";
      $result = mysqli_query($connection, $query);
      $pName = mysqli_fetch_array($result);
      $pName = $pName['nazev'];
    }
  }
	//Mazání cookie current
	setcookie("current", "", time()-3600);
	
	if (!empty($pId))	//Část zvolena
	{
		$pArr = array($pId, $pName, $everything);
		$_SESSION['current'] = $pArr;
	}
	else if (!isset($_SESSION['current']))	//Část nezvolena ani nenastavena --> přesměrování na stránku s výběrem
	{
		echo "<script type='text/javascript'>location.href = 'list.php';</script>";
	}
?>
<!DOCTYPE html>
<html>
    <head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width" />
		<style>
		    <?php 
		        require 'php/included/themeHandler.php';
		    ?>
		</style>
		<link rel="stylesheet" type="text/css" href="css/css.css">
		<link rel="icon" href="images/favicon.ico">
		<link rel="apple-touch-icon" sizes="180x180" href="images/apple-touch-icon.png">
		<link rel="icon" type="image/png" sizes="32x32" href="images/icon-32x32.png">
		<link rel="icon" type="image/png" sizes="16x16" href="images/icon-16x16.png">
		<link rel="manifest" href="manifest.json">
		<link rel="mask-icon" href="images/safari-pinned-tab.svg" color="#ffc835">
		<meta name="theme-color" content="#ffffff">
        <title>Menu: <?php echo $_SESSION['current'][1]; ?></title>
    </head>
    <body>
    <div class="container">
        <header>  				
            <div id="menuHeading">
				<?php echo $_SESSION['current'][1]; ?>
				(<a href="list.php">Změnit</a>)
			</div>
        </header>
        <main id="main_menu">
    	    <a href="addPics.php">
	           <div id="btn1" class="menu" onclick="addPics()">Přidat obrázky</div>
	        </a>
	           <a href="learn.php">
	           <div id="btn2" class="menu" onclick="learn()">Učit se</div>
            </a>
                <?php 
                  if ($_SESSION['current'][2] !== true)
                  {
                    $query = "SELECT obrazky FROM casti WHERE casti_id = ".mysqli_real_escape_string($connection, $_SESSION['current'][0]);
                    $result = mysqli_query($connection, $query);
                    $result = mysqli_fetch_array($result);
                    $result = $result['obrazky'];
                    if (empty($result))
                    {
                        echo "<a>";
                        echo "<div id='btn3' class='menu' style='background-color: #CCCCCC;text-decoration: line-through;transition: none;color:#FFFFFF;cursor: not-allowed;'>Vyzkoušet se</div>";
                    }
                    else
                    {
                        echo "<a href='test.php'>";
                        echo "<div id='btn3' class='menu' onclick='test()'>Vyzkoušet se</div>";
                    }
                  }
                  else
                  {
                      echo "<a href='test.php'>";
                      echo "<div id='btn3' class='menu' onclick='test()'>Vyzkoušet se</div>";
                  }
                ?>
            </a>  
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
