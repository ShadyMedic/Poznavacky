<?php
    if (session_status() == PHP_SESSION_NONE){include 'included/httpStats.php';} //Statistika se zaznamenává, pouze pokud je skript zavolán jako AJAX
    
    echo "<table id='listTable'>
                <tr  class='main_tr'>
                    <td>Název třídy</td>
                    <td>Poznávačky</td>
                    <td>Vstupní kód</td>
                </tr>
            ";
    
    $query = "SELECT * FROM tridy";
    $result = mysqli_query($connection, $query);
    while ($info = mysqli_fetch_array($result))
    {
        echo '<tr class="listRow" onclick="choose(1,'.$info['tridy_id'].')">';
        echo '<td class="listNames">'.$info['nazev'].'</td>';
        echo '<td class="listNames">'.$info['skupiny'].'</td>';
        echo '<td class="listNaturals">'.$info['kod'].'</td>';
        echo '</tr>';
    }
    echo "</table>
    <button class='button' onclick='newClass()'>Zažádat o vytvoření nové třídy</button>";
    
    //Aktualizovat uživateli poslední prohlíženou složku
    if (session_status() == PHP_SESSION_NONE){session_start();} //Session se startuje, pouze pokud je skript zavolán jako AJAX
    $userId = $_SESSION['user']['id'];
    $userId = mysqli_real_escape_string($connection, $userId);
    $query = "UPDATE uzivatele SET posledni_uroven = 0, posledni_slozka = NULL WHERE uzivatele_id=$userId LIMIT 1";
    $result = mysqli_query($connection, $query);
    