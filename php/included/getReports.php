<?php
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
    
    $query = "SELECT obrazekId,duvod,dalsiInformace,pocet FROM hlaseni ORDER BY pocet DESC LIMIT 25";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        echo mysqli_error($connection);
    }
    
    echo "<table border=1>";
    echo "<th>Zdroj</th><th>Důvod</th><th>Další informace</th><th>Počet nahlášení</th><th>Akce</th>";
        while ($report = mysqli_fetch_array($result))
        {
            echo "<tr>";
                //Získání URL obrázku
                $picId = $report['obrazekId'];
                $query = "SELECT zdroj FROM obrazky WHERE id=$picId";
                $innerresult = mysqli_query($connection, $query);
                $innerresult = mysqli_fetch_array($innerresult);
                $url = $innerresult['zdroj'];
                echo "<td><a href='$url' target='_blank'>$url</a></td>";
    
                //Výpis důvodu
                $reason = $report['duvod'];
                echo "<td>";
                switch ($reason)
                {
                    case 0:
                        echo "Obrázek se nezobrazuje správně";
                        break;
                    case 1:
                        echo "Obrázek se načítá příliš dlouho";
                        break;
                    case 2:
                        echo "Obrázek zobrazuje nesprávnou přírodninu";
                        break;
                    case 3:
                        echo "Obrázek obsahuje název přírodniny";
                        break;
                    case 4:
                        echo "Obrázek má příliš špatné rozlišení";
                        break;
                    case 5:
                        echo "Obrázek porušuje autorská práva";
                        break;
                    case 6:
                        echo "Jiný důvod";
                }
                echo "</td>";
                
                //Výpis přídavných informací
                $info = $report['dalsiInformace'];
                if ($reason == 6)
                {
                    echo "<td><i title='$info'>Najedďte sem myší pro zobrazení důvodu</i></td>";
                }
                else
                {
                    echo "<td>$info</td>";
                }
                
                //Výpis počtu nahlášení
                $reporters = $report['pocet'];
                echo "<td>$reporters</td>";
                
                //Výpis akcí
                echo "<td>";
                    echo "<button class='reportAction activeBtn' onclick='showPicture(event)' title='Zobrazit obrázek'>";
                        echo "<img src='images/eye.gif'/>";
                    echo "</button>";
                    echo "<button class='reportAction activeBtn' onclick='disablePicture(event)' title='Skrýt obrázek'>";
                        echo "<img src='images/dot.gif'/>";
                    echo "</button>";
                    echo "<button class='reportAction activeBtn' onclick='deletePicture(event)' title='Odstranit obrázek'>";
                        echo "<img src='images/cross.gif'/>";
                    echo "</button>";
                    echo "<button class='reportAction activeBtn' onclick='deleteReport(event)' title='Odstranit hlášení'>";
                        echo "<img src='images/minus.gif'/>";
                    echo "</button>";
                echo "</td>";
            echo "</tr>";
        }
    echo "</table>";
    
