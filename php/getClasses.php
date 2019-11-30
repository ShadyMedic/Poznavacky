<?php
    if (session_status() == PHP_SESSION_NONE){include 'included/httpStats.php';} //Statistika se zaznamenává, pouze pokud je skript zavolán jako AJAX
    
    echo "<table id='listTable'>
                <tr>
                    <th>Název třídy</th>
                    <th>Poznávačky</th>
                    <th>Vstupní kód</th>
                </tr>
            ";
    
    $query = "SELECT * FROM tridy";
    $result = mysqli_query($connection, $query);
    while ($info = mysqli_fetch_array($result))
    {
        echo '<tr class="listRow" onclick="choose(1,'.$info['id'].')">';
        echo '<td class="listNames">'.$info['nazev'].'</td>';
        echo '<td class="listNames">'.$info['skupiny'].'</td>';
        echo '<td class="listNaturals">'.$info['kod'].'</td>';
        echo '</tr>';
    }
    echo "</table>";
    
    //Aktualizovat uživateli poslední prohlíženou složku
    if (session_status() == PHP_SESSION_NONE){session_start();} //Session se startuje, pouze pokud je skript zavolán jako AJAX
    $userId = $_SESSION['user']['id'];
    mysqli_real_escape_string($connection, $query);
    $query = "UPDATE uzivatele SET posledniUroven = 0, posledniSlozka = NULL WHERE id=$userId LIMIT 1";
    $result = mysqli_query($connection, $query);
    