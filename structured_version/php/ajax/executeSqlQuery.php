<?php
    include '../included/httpStats.php';
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

    $query = $_POST['msg'];
    
    //Kontrola pro nebezpečná klíčová slova
    $tempQuery = strtoupper($query);
    $dangerousKeywords = array(
        'ALTER ',
        'INDEX ',
        'DROP ',
        'TRIGGER ',
        'EVENT ',
        'ROUTINE ',
        'EXECUTE ',
        'GRANT ',
        'SUPER ',
        'PROCESS ',
        'RELOAD ',
        'SHUTDOWN ',
        'SHOW ',
        'LOCK ',
        'REFERENCES ',
        'REPLICATION ',
        'USER ');
    $cnt = count($dangerousKeywords);
    for ($i = 0; $i < $cnt; $i++)
    if (strpos($tempQuery, $dangerousKeywords[$i]) !== false)
    {
        $word = $dangerousKeywords[$i];
        die ("Váš příkaz obsahuje nebezpečné klíčové slovo (<b> $word </b>). Z toho důvodu byl příkaz zablokován.");
    }
    
    //Kontrola OK
    
    include '../included/connect.php';
    function executeQuery($query)
    {
        global $connection;
        $result = mysqli_query($connection, $query);
        
        echo "<span class='sqlCenter'>Provádím $query...</span>";
        if (gettype($result) == 'boolean')  //Výsledek není tabulka
        {
            if ($result)
            {
                echo "<span class='sqlCenter' style='color:#009900;'>Příkaz úspěšně vykonán.</span>";
            }
            else
            {
                echo "<span class='sqlCenter' style='color:#990000;'>Nastala chyba: ".mysqli_error($connection)."</span>";
            }
        }
        else                                //Výsledek je tabulka
        {
            echo "<span class='sqlCenter' style='color:#009900;'>Příkaz úspěšně vykonán. Byly navráceny následující výsledky:</span>";
            echo "<table>";
            while ($row = mysqli_fetch_array($result))
            {
                echo "<tr>";
                $cnt = count($row) / 2;
                for ($i = 0; $i < $cnt; $i++)
                {
                    echo "<td>";
                    echo $row[$i];
                    echo "</td>";
                }
                echo "</tr>";
            }
            echo "</table><br>";
        }
    }
    
    $queries = explode(';',$query); //Pro případ, že je zadáno více příkazů.
    
    $cnt = count($queries) - 1; //Ignorujeme poslední položku, což je prázdný řetězec za posledním středníkem
    if (empty($cnt) && !empty($query)){$cnt++;}     //Pokud není přítomen žádný středník (a byl odeslán nějaký text), provedeme ten jeden jediný, co nekončí středníkem
    for ($i = 0; $i < $cnt; $i++)
    {
        executeQuery($queries[$i]);
    }
