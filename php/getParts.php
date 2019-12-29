<?php
if (session_status() == PHP_SESSION_NONE){include 'included/httpStats.php';} //Statistika se zaznamenává, pouze pokud je skript zavolán jako AJAX
    
    $groupId = $_GET['groupId'];
    
    $groupId = mysqli_real_escape_string($connection, $groupId);
    
    //Získání id třídy pro tlačítko návrat
    $query = "SELECT tridy.tridy_id FROM tridy INNER JOIN poznavacky ON poznavacky.tridy_id = tridy.tridy_id WHERE poznavacky.poznavacky_id = $groupId";
    $result = mysqli_query($connection, $query);
    $result = mysqli_fetch_array($result);
    $classId = $result['tridy_id'];
    
    echo "<table id='listTable'>
        <tr class='listRow' onclick='choose(1, $classId)'>
            <td class='listBack' colspan=3><i>Zpět na seznam poznávaček</i></td>
        </tr>
        <tr>
            <th>Název části</th>
            <th>Přírodniny</th>
            <th>Obrázky</th>
        </tr>
        ";
    
    $query = "SELECT * FROM casti WHERE poznavacky_id = $groupId";
    $result = mysqli_query($connection, $query);
    if (mysqli_num_rows($result) === 0)
    {
        echo '<tr class="infoRow">';
        echo '<td class="listNames" colspan=2>V této poznávačce zatím nejsou žádné skupiny</td>';
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
        $txt = "choose(3,".$info['casti_id'].")";
        echo "<tr class='listRow' onclick=$txt>";
        echo '<td class="listNames">'.$info['nazev'].'</td>';
        echo '<td class="listNaturals">'.$info['prirodniny'].'</td>';
        echo '<td class="listPictures">'.$info['obrazky'].'</td>';
        echo '</tr>';
    }
    if ($multiple === true)     //Vypsání řádky pro výběr všech poznávaček (argument funkce je seznam ID částí oddělený čárkami)
    {
        $txt = "choose(3,'".implode($partsIds,',')."')";
        //$txt = "choose(3,".$info['id'].")";
        
        echo "<tr class='listRow' onclick=$txt>";
        echo '<td class="listNames">Vše</td>';
        echo '<td class="listNaturals">'.$totalNaturals.'</td>';
        echo '<td class="listPictures">'.$totalPics.'</td>';
        echo '</tr>';
    }
    echo "</table>";
    
    //Aktualizovat uživateli poslední prohlíženou složku
    if (session_status() == PHP_SESSION_NONE){session_start();} //Session se startuje, pouze pokud je skript zavolán jako AJAX
    $userId = $_SESSION['user']['id'];
    $groupId = mysqli_real_escape_string($connection, $groupId);
    $userId = mysqli_real_escape_string($connection, $userId);
    $query = "UPDATE uzivatele SET posledni_uroven = 2, posledni_slozka = $groupId WHERE uzivatele_id=$userId LIMIT 1";
    $result = mysqli_query($connection, $query);