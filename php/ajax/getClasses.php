<?php
    include '../included/httpStats.php';
    
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