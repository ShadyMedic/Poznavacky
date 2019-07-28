<?php
    session_start();
    
    include 'httpStats.php'; //Zahrnuje connect.php
    
    $email = $_POST['email'];
    
    //Kontrola délky e-mailu (aby nevznikaly dlouhé SQL dotazy)
    if(strlen($email) > 255)
    {
        header("Location: index.php");
        $_SESSION['passwordRecoveryError'] = "Email nesmí být delší než 255 znaků.";
        die();
    }
    
    //Kontrola platného e-mailu
    if(!filter_var($email, FILTER_VALIDATE_EMAIL))
    {
        header("Location: index.php");
        $_SESSION['passwordRecoveryError'] = "E-mail nemá platný formát.";
        die();
    }
    
    //E-MAIL JE OK
    
    //Kontrola existence e-mailu v databázi
    $query = "SELECT id FROM uzivatele WHERE email = '$email' LIMIT 1";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        header("Location: errSql.html");
        die();
    }
    if (mysqli_num_rows($result) == 0)
    {
        header("Location: index.php");
        $_SESSION['passwordRecoveryError'] = "K této e-mailové adrese není přidružen žádný účet.";
        die();
    }
    
    //Uživatel nalezen
    $result = mysqli_fetch_array($result);
    $userId = $result['id'];
    
    //Vygenerovat kód
    $done = false;
    $code = NULL;
    do
    {
        //Vygenerovat třicetidvoumístný kód pro obnovení hesla
        $code = bin2hex(random_bytes(16));   //128 bitů --> maximálně třicetidvoumístný kód
        
        //Zkontrolovat, zda již kód v databázi neexistuje
        $query = "SELECT uzivatel_id FROM obnovenihesel WHERE kod='$code' LIMIT 1";
        $result = mysqli_query($connection, $query);
        if (!$result)
        {
            header("Location: errSql.html");
            die();
        }
        if (!mysqli_num_rows($result) > 0)  //Kontrola případné potřeby opakování generování kódu
        {
           $done = true;
        }
    }while ($done == false);
    
    //Smazat starý kód z databáze (pokud existuje)
    $query = "DELETE FROM obnovenihesel WHERE uzivatel_id=$userId";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        header("Location: errSql.html");
        die();
    }
    
    //Uložit kód do databáze
    $query = "INSERT INTO obnovenihesel (kod, uzivatel_id) VALUES ('".md5($code)."', $userId)";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        header("Location: errSql.html");
        die();
    }
?>

<html>
	<head>
		<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
	</head>
	<body>

<?php
    //Poslat e-mail.
    include 'emailSender.php';
    echo sendEmail(
        $email, 
        'Žádost o obnovu hesla na poznavacky.chytrak.cz', 
        "<span>Pro obnovení vašeho hesla klikněte na tento odkaz: </span>".
        "<a href='localhost/Poznavacky/emailPasswordRecovery.php?token=$code'>OBNOVIT HESLO</a>".
        "<br>".
        "<span>Tento odkaz bude platný po následujících 24 hodin, nebo do odeslání žádosti o nový kód.</span>".
        "<br>".
        "<span style='color: #990000; font-weight: bold;'>DŮLEŽITÉ: </span>".
        "<span style='color: #990000;'>Tento e-mail nikomu nepřeposílejte! Mohl by získat přístup k vašemu účtu.</span>"
        );
?>
		<a href="index.php" style="height: fit-content; display: block; position: absolute; top: 0; bottom: 0; left: 0; right: 0; margin: auto; text-align: center; font-size: 4em;">Zpět na úvodní stránku</a>
	</body>
</html>