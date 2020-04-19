<?php
    if (session_status() == PHP_SESSION_NONE)
    {
        include 'included/httpStats.php'; //Statistika se zaznamenává, pouze pokud je skript zavolán jako AJAX
        session_start(); //Sezení se zahajuje pouze v případě, že již nebylo zahájeno
    }
    
    $userId = $_SESSION['user']['id'];
    $groupId = $_GET['groupId'];
    
    if (empty($_SESSION['class']))
    {
        global $classId;
        
        //Získání ID třídy, do které patří zvolená poznávačka
        $query = "SELECT tridy_id FROM poznavacky WHERE poznavacky_id = $groupId LIMIT 1";
        $result = mysqli_query($connection, $query);
        $cId = mysqli_fetch_array($result)['tridy_id'];
        
        //Kontrola, zda je přihlášený uživatel členem dané třídy
        $query = "SELECT COUNT(*) AS 'cnt' FROM tridy WHERE tridy_id = $cId AND (status = 'public' OR (SELECT COUNT(*) FROM clenstvi WHERE uzivatele_id = $userId AND tridy_id = $cId) > 0);";
        $result = mysqli_query($connection, $query);
        $count = mysqli_fetch_array($result)['cnt'];
        if ($count < 1)
        {
            //Zamítnutí přístupu
            die("<div style='color: #990000; font-weight: bold;'>Přístup do třídy s touto poznávačkou odepřen!</div><br><button class='button' onclick='choose(0)'>Zpět na seznam tříd</button>");
        }
        else
        {
            $_SESSION['class'] = $cId;
        }
    }
    
    $classId = $_SESSION['class'];
    
    $userId = mysqli_real_escape_string($connection, $userId);
    $groupId = mysqli_real_escape_string($connection, $groupId);
    $classId = mysqli_real_escape_string($connection, $classId);
    
    //Získávání částí - v SQL dotazu se dotazujeme na části, které patří do poznávačky, která byla zvolen uživatelem A ZÁROVEŇ patří do dříve zvolené třídy
    //Tím je znemožněn výběr částí, které patří do poznávaček, které patří do třídy jejíž není uživatel členem
    $query = "SELECT * FROM casti WHERE poznavacky_id = (SELECT poznavacky_id FROM poznavacky WHERE poznavacky_id = $groupId AND tridy_id = $classId LIMIT 1);";
    $result = mysqli_query($connection, $query);
    if(!$result){die($query);}
    
    echo "<table id='listTable'>
        <tr class='main_tr'>
            <td class='listNames listPoznavacky'>Název části</td>
            <td class='listNaturals listPoznavacky'>Přírodniny</td>
            <td class='listPics listPoznavacky'>Obrázky</td>
        </tr>
        "; 
    
    if (mysqli_num_rows($result) === 0)
    {
        echo '<tr class="infoRow">';
        echo '<td class="listNames listEmpty" colspan=3>V této poznávačce zatím nejsou žádné skupiny.</td>';
        echo '</tr>';
    }
    $multiple = false;    //Určuje, jestli bude vygenerována řádka pro výběr všech částí (pokud je počet částí > 1)
    $partsIds = array();  //Skladuje ID všech částí v této poznávačce, aby mohly být poslány při výběru všech částí
    $totalNaturals = 0;   //Počítá celkový počet přírodnin v poznávačce, aby se číslo zobrazilo v řádce se všemi částmi
    $totalPics = 0;       //Počítá celkový počet obrázků v poznávačce, aby se číslo zobrazilo v řádce se všemi částmi
    if (mysqli_num_rows($result) > 1)
    {
        $multiple = true;
    }
    while ($info = mysqli_fetch_array($result))
    {
        array_push($partsIds, $info['casti_id']);
        $totalNaturals += $info['prirodniny'];
        $totalPics += $info['obrazky'];
        $hasPictures = ($info['obrazky'] > 0)? "true" : "false";
        $txt = "showOptions(event,".$info['casti_id'].",$hasPictures)";
        echo "<tr class='listRow' onclick=$txt>";
        echo '<td class="listNames listPoznavacky">'.$info['nazev'].'</td>';
        echo '<td class="listNaturals listPoznavacky">'.$info['prirodniny'].'</td>';
        echo '<td class="listPictures listPoznavacky">'.$info['obrazky'].'</td>';
        echo '</tr>';
    }
    if ($multiple === true)     //Vypsání řádky pro výběr všech poznávaček (argument funkce je seznam ID částí oddělený čárkami)
    {
        $hasPictures = ($totalPics > 0)? "true" : "false";
        $txt = "showOptions(event,'".implode($partsIds,',')."',$hasPictures)";
        
        echo "<tr class='listRow' onclick=$txt>";
        echo '<td class="listNames listPoznavacky">Vše</td>';
        echo '<td class="listNaturals listPoznavacky">'.$totalNaturals.'</td>';
        echo '<td class="listPictures listPoznavacky">'.$totalPics.'</td>';
        echo '</tr>';
    }
    echo "</table>
    <button class='button' onclick='choose(1, $classId)'>Zpět na seznam poznávaček</button>";
    
    //Aktualizovat uživateli poslední prohlíženou složku
    $query = "UPDATE uzivatele SET posledni_uroven = 2, posledni_slozka = $groupId WHERE uzivatele_id=$userId LIMIT 1";
    $result = mysqli_query($connection, $query);