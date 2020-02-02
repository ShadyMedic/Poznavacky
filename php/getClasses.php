<?php
    if (session_status() == PHP_SESSION_NONE)
    {
        include 'included/httpStats.php'; //Statistika se zaznamenává, pouze pokud je skript zavolán jako AJAX
        session_start(); //Session se startuje, pouze pokud je skript zavolán jako AJAX
    }
    echo "<table id='listTable'>
                <tr  class='main_tr'>
                    <td>Název třídy</td>
                    <td>Poznávačky</td>
                    <td>Vstupní kód</td>
                </tr>
            ";
    
    $userId = $_SESSION['user']['id'];
    $query = "SELECT * FROM `tridy` WHERE status = 'public' OR tridy_id IN (SELECT tridy_id FROM clenstvi WHERE uzivatele_id = $userId);";
    $result = mysqli_query($connection, $query);
    if (mysqli_num_rows($result) === 0)
    {
        echo '<tr class="infoRow">';
        echo '<td class="listNames listEmpty" colspan=3>Zatím nemáte přístup do žádné třídy.</td>';
        echo '</tr>';
    }
    while ($info = mysqli_fetch_array($result))
    {
        echo '<tr class="listRow" onclick="choose(1,'.$info['tridy_id'].')">';
        echo '<td class="listNames">'.$info['nazev'].'</td>';
        echo '<td class="listNames">'.$info['skupiny'].'</td>';
        echo '<td class="listNaturals">'.$info['kod'].'</td>';
        echo '</tr>';
    }
    echo "</table>
    <button class='button' onclick='newClass()'>Zažádat o vytvoření nové třídy</button>
    <div id='classCodeBtn' style='display:block;'>
        <button class='button' onclick='enterClassCode()'>Zadat kód soukromé třídy</button>
    </div>
    <div id='classCodeForm' style='display:none;'>
        <input id='classCodeInput' type=text maxlength=4 style='width: 2rem;'/>
        <button class='button' onclick='submitClassCode()'>OK</button>
        <button class='button' onclick='closeClassCode()'>Zpět</button>
    </div>";
    
    //Aktualizovat uživateli poslední prohlíženou složku
    $userId = $_SESSION['user']['id'];
    $userId = mysqli_real_escape_string($connection, $userId);
    $query = "UPDATE uzivatele SET posledni_uroven = 0, posledni_slozka = NULL WHERE uzivatele_id=$userId LIMIT 1";
    $result = mysqli_query($connection, $query);
    