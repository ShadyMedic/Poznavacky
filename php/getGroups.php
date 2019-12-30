<?php
    if (session_status() == PHP_SESSION_NONE){include 'included/httpStats.php';} //Statistika se zaznamenává, pouze pokud je skript zavolán jako AJAX
    
    $classId = $_GET['classId'];
    
    $classId = mysqli_real_escape_string($connection, $classId);
    
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
    if (session_status() == PHP_SESSION_NONE){session_start();} //Session se startuje, pouze pokud je skript zavolán jako AJAX
    $userId = $_SESSION['user']['id'];
    $classId = mysqli_real_escape_string($connection, $classId);
    $userId = mysqli_real_escape_string($connection, $userId);
    $query = "UPDATE uzivatele SET posledni_uroven = 1, posledni_slozka = $classId WHERE uzivatele_id=$userId LIMIT 1";
    $result = mysqli_query($connection, $query);
    