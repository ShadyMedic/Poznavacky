<?php
    session_start();
    
    include 'connect.php';

    $newName = $_GET['new'];
    
    //Ochrana před SQL injekcí
    $newName = mysqli_real_escape_string($connection, $newName);
    
    //Kontrola unikátnosti jména
    $query = "SELECT jmeno FROM uzivatele WHERE jmeno='$newName' UNION SELECT nove FROM zadostijmena WHERE nove='$newName' LIMIT 1";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        echo "Vyskytla se chyba při práci s databází. \nPro více informací přejděte na ".$_SERVER['SERVER_NAME']."/errSql.html";
        die();
    }
    if (mysqli_num_rows($result) > 0)
    {
        echo "Jiný uživatel již toto jméno používá, nebo o změnu na něj zažádal.";
        die();
    }
    
    //Kontrola znaků ve jméně
    if(strlen($newName) !== strspn($newName, '0123456789aábcčdďeěéfghiíjklmnňoópqrřsštťuůúvwxyýzžAÁBCČDĎEĚÉFGHIÍJKLMNŇOÓPQRŘSŠTŤUŮÚVWXYZŽ '))
    {
        echo "Jméno může obsahovat pouze písmena, číslice a mezery.";
        die();
    }
    
    //Kontrola délky jména
    if (strlen($newName) < 4)
    {
        alert("Jméno musí být alespoň 4 znaky dlouhé.");
        return;
    }
    if (strlen($newName) > 15)
    {
        alert("Jméno nesmí být více než 15 znaků dlouhé.");
        return;
    }
    
    //KONTROLA DAT V POŘÁDKU
    
    $oldName = $_SESSION['user'];
    $oldName = $oldName['name'];
    
    //Kontrola, zda již uživatel na nějakou nevyřízenou změnu nečeká
    $query = "SELECT id FROM zadostijmena WHERE puvodni='$oldName' LIMIT 1";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        echo "Vyskytla se chyba při práci s databází. \nPro více informací přejděte na ".$_SERVER['SERVER_NAME']."/errSql.html";
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
            echo "Vyskytla se chyba při práci s databází. \nPro více informací přejděte na ".$_SERVER['SERVER_NAME']."/errSql.html";
            die();
        }
        echo "O změnu jména bylo zažádáno. \nNové jméno bude co nejdříve zkontrolováno a případně nahradí vaše stávající jméno. \n\nTato žádost o změnu přepsala vaší nevyřízenou žádost o změnu jména z minulosti.";
    }
    else
    {
        //Ukládání žádosti
        $query = "INSERT INTO zadostijmena (puvodni, nove, cas) VALUES ('$oldName', '$newName', ".time().")";
        $result = mysqli_query($connection, $query);
        if (!$result)
        {
            echo "Vyskytla se chyba při práci s databází. Pro více informací přejděte na ".$_SERVER['SERVER_NAME']."/errSql.html";
            die();
        }
        echo "O změnu jména bylo zažádáno. \nNové jméno bude co nejdříve zkontrolováno a případně nahradí vaše stávající jméno.";
    }