<?php
	$redirectIn = false;
	$redirectOut = true;
	require 'verification.php';
	
	//Mazání sezení
	session_start();
	$_SESSION = array();
	$cookie_par = session_get_cookie_params();
	setcookie(session_name(), '', time() - 86400, $cookie_par['path'], $cookie_par['domain'], $cookie_par['secure'], $cookie_par['httponly']);
	session_destroy();
	
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<link rel="stylesheet" type="text/css" href="css.css">
		<script type="text/javascript" src="list.js"></script>
		<title>Poznávačky</title>
	</head>
	<body>
        <header>
            <h1>Dostupné poznávačky</h1>
        </header>
        <main>
            <table id="listTable">
		 	    <tr>
    		 		<th>Název</th>
    		 		<th>Přírodniny</th>
    		 		<th>Obrázky</th>
    		 	</tr>
    		 	<?php
    				//Seznam dostupných poznávaček
    				include 'connect.php';
    				
    				$query = 'SELECT * FROM poznavacky';
    				$result = mysqli_query($connection,$query);
    				while ($info = mysqli_fetch_array($result))
    				{
    					echo '<tr class="listRow" onclick="choose(\''.$info['id'].'&'.$info['nazev'].'\')">';
    						echo '<td class="listNames">'.$info['nazev'].'</td>';
    						echo '<td class="listNaturals">'.$info['prirodniny'].'</td>';
    						echo '<td class="listPics">'.$info['obrazky'].'</td>';
    					echo '</tr>';
    				}
    			?> 
      		</table>
         </main>
	</body>
</html>