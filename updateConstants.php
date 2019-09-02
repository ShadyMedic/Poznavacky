<?php
    include 'httpStats.php';     //Obsahuje session_start();
    include 'emailSender.php';
    session_start();
    
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
        die();
    }
    
    //Získání stringu obsahující jména a hodnoty konstant
    $data = $_POST['msg'];
    
    $textToWrite = array();
    
    //Rozdělování přijmutých dat na jednotlivé konstanty
    $constants = explode('¶', $data);
    foreach ($constants as $constant)
    {
        //Oddělit název a hodnotu
        $name = explode('¤', $constant)[0];
        $value = explode('¤', $constant)[1];
        
        //Zjistit, zda je potřeba orámovat hodnotu apostrofy a případně je přidat
        if (is_numeric($value) || $value === 'false' || $value === 'true')
        {
            $statement = 'define(\''.$name.'\', '.$value.');';
        }
        else
        {
            $statement = 'define(\''.$name.'\', \''.$value.'\');';
        }
        
        //Přidat hodnotu do fronty k zápisu
        array_push($textToWrite, $statement);
    }
    //Pole pozpátku, abychom mohli používat array_pop() pro postupné čtení hodnot od začátku
    $textToWrite = array_reverse($textToWrite);
    
    //Načtení současného obsahu souboru
    $fileContent = file('CONSTANTS.php');
    
    //Otevření souboru pro zápis (obsah je vymazán)
    $file = fopen('CONSTANTS.php', 'w');
    
    //Postupné projetí souboru a nahrazení řádků s konstantami novými
    foreach($fileContent as $fileLine)
    {
        if (strpos($fileLine, 'define') === 0)  //Řádka obsahuje definici konstanty --> nahrazení novou definicí
        {
            fwrite($file, array_pop($textToWrite).PHP_EOL);
        }
        else                                    //Řádka neobsahuje definici konstanty --> ponechání původní řádky
        {
            fwrite($file, $fileLine);
        }
    }
    if (count($textToWrite) > 0)    //Pokud zbývají nějaké nezapsané konstanty, zapíšeme je na konec souboru
    {
        foreach($textToWrite as $constant)
        {
            fwrite($file, array_pop($textToWrite).PHP_EOL);
        }
    }
    
    fclose($file);
    
    echo "alert('Konstanty byly úspěšně aktualizovány.');";