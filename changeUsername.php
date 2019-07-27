<?php
    session_start();
    
    include 'connect.php';
    include 'logger.php';

    $newName = $_GET['new'];
    
    $oldName = $_SESSION['user'];
    $oldName = $oldName['name'];
    
    //Ochrana před SQL injekcí
    $newName = mysqli_real_escape_string($connection, $newName);
    
    //Kontrola unikátnosti jména
    $query = "SELECT jmeno FROM uzivatele WHERE jmeno='$newName' UNION SELECT nove FROM zadostijmena WHERE nove='$newName' LIMIT 1";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        echo "swal('Vyskytla se chyba při práci s databází.','Pro více informací přejděte na ".$_SERVER['SERVER_NAME']."/errSql.html','error')";
        die();
    }
    if (mysqli_num_rows($result) > 0)
    {
        filelog("Uživatel $oldName se pokusil změnit si jméno na $newName, avšak neuspěl kvůli neunikátnímu jménu.");
        echo "swal('Jiný uživatel již toto jméno používá, nebo o změnu na něj zažádal.','','error')";
        die();
    }
    
    //Kontrola znaků ve jméně
    if(strlen($newName) !== strspn($newName, '0123456789aábcčdďeěéfghiíjklmnňoópqrřsštťuůúvwxyýzžAÁBCČDĎEĚÉFGHIÍJKLMNŇOÓPQRŘSŠTŤUŮÚVWXYZŽ '))
    {
        echo "swal('Jméno může obsahovat pouze písmena, číslice a mezery.','','error')";
        die();
    }
    
    //Kontrola délky jména
    if (strlen($newName) < 4)
    {
        filelog("Uživatel $oldName se pokusil změnit si jméno, avšak neuspěl kvůli krátkému jménu.");
        echo "swal('Jméno musí být alespoň 4 znaky dlouhé.','','error')";
        die();
    }
    if (strlen($newName) > 15)
    {
        filelog("Uživatel $oldName se pokusil změnit si jméno, avšak neuspěl kvůli dlouhému jménu.");
        echo "swal('Jméno nesmí být více než 15 znaků dlouhé.','','error')";
        die();
    }
    
    //KONTROLA DAT V POŘÁDKU

    //Kontrola, zda již uživatel na nějakou nevyřízenou změnu nečeká
    $query = "SELECT id FROM zadostijmena WHERE puvodni='$oldName' LIMIT 1";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        echo "swal('Vyskytla se chyba při práci s databází.','Pro více informací přejděte na ".$_SERVER['SERVER_NAME']."/errSql.html','error')";
        die();
    }
    if (mysqli_num_rows($result) > 0)
    {
        $requestId = mysqli_fetch_array($result)['id'];
        
        //Přepisování žádosti
        $query = "UPDATE zadostijmena SET nove = '$newName', cas = ".time()." WHERE id = $requestId";
        $result = mysqli_query($connection, $query);
        if (!$result)
        {
            echo "swal('Vyskytla se chyba při práci s databází.','Pro více informací přejděte na ".$_SERVER['SERVER_NAME']."/errSql.html','error')";
            die();
        }
        $ip = $_SERVER['REMOTE_ADDR'];
        filelog("Uživatel $oldName se změnil svou žádost o nové jméno na $newName z IP adresy $ip.");
        echo "swal('O změnu jména bylo zažádáno.','Nové jméno bude co nejdříve zkontrolováno a případně nahradí vaše stávající jméno. \nTato žádost o změnu přepsala vaší nevyřízenou žádost o změnu jména z minulosti.','success')";
    }
    else
    {
        //Ukládání žádosti
        $query = "INSERT INTO zadostijmena (puvodni, nove, cas) VALUES ('$oldName', '$newName', ".time().")";
        $result = mysqli_query($connection, $query);
        if (!$result)
        {
            echo "swal('Vyskytla se chyba při práci s databází. Pro více informací přejděte na ".$_SERVER['SERVER_NAME']."/errSql.html','','error')";
            die();
        }
        $ip = $_SERVER['REMOTE_ADDR'];
        filelog("Uživatel $oldName zažádal o změnu jména na $newName z IP adresy $ip.");
        echo "swal('O změnu jména bylo zažádáno.','Nové jméno bude co nejdříve zkontrolováno a případně nahradí vaše stávající jméno.','success')";
    }