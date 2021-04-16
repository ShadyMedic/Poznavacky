<?php
    if (session_status() == PHP_SESSION_NONE)
    {
        include 'included/httpStats.php';  //Statistika se zaznamenává, pouze pokud je skript zavolán jako AJAX
        session_start(); //Sezení se zahajuje pouze v případě, že již nebylo zahájeno
    }
    
    $classId = $_GET['classId'];
    $userId = $_SESSION['user']['id'];
    
    $classId = mysqli_real_escape_string($connection, $classId);
    $userId = mysqli_real_escape_string($connection, $userId);
    
    //Kontrola, zda má uživatel do třídy přístup
    $query = "SELECT COUNT(*) AS 'cnt' FROM tridy WHERE tridy_id = $classId AND (status = 'public' OR (SELECT COUNT(*) FROM clenstvi WHERE uzivatele_id = $userId AND clenstvi.tridy_id = tridy.tridy_id) > 0);";
    $result = mysqli_query($connection, $query);
    $result = mysqli_fetch_array($result);
    if ($result['cnt'] < 1)
    {
        //Odepření přístupu
        die("<div style='color: #990000; font-weight: bold;'>Přístup do třídy odepřen!</div><br><button class='button' onclick='choose(0)'>Zpět na seznam tříd</button>");
    }
    //Nastavování zvolené třídy do $_SESSION
    $_SESSION['class'] = $classId;
    
    echo "<table id='listTable'>
        <tr class='main_tr'>
            <td>Název poznávačky</td>
            <td>Části</td>
        </tr>
    ";
    
    $query = "SELECT * FROM poznavacky WHERE tridy_id = $classId";
    $result = mysqli_query($connection, $query);
    if (mysqli_num_rows($result) === 0)
    {
        echo '<tr class="infoRow">';
            echo '<td class="listNames listEmpty" colspan=3>V této třídě zatím nejsou žádné poznávačky.</td>';
        echo '</tr>';
    }
    while ($info = mysqli_fetch_array($result))
    {
    echo '<tr class="listRow" onclick="choose(2,'.$info['poznavacky_id'].')">';
        echo '<td class="listNames">'.$info['nazev'].'</td>';
        echo '<td class="listNaturals">'.$info['casti'].'</td>';
    echo '</tr>';
    }
    echo "</table>
    <button class='button' onclick='choose(0)'>Zpět na seznam tříd</button>";
    
    //Aktualizovat uživateli poslední prohlíženou složku
    $query = "UPDATE uzivatele SET posledni_uroven = 1, posledni_slozka = $classId WHERE uzivatele_id=$userId LIMIT 1";
    $result = mysqli_query($connection, $query);
    