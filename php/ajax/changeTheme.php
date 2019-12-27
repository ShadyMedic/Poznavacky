<?php
    session_start();
    
    include '../included/httpStats.php'; //Zahrnuje connect.php
    include '../included/logger.php';
    
    $themeId = urldecode($_POST['newName']);
    
    $userdata = $_SESSION['user'];
    $userId = $userdata['id'];
    $userName = $userdata['name'];
    
    //Kontrola id vzhledu
    if (!in_array($themeId, ['1','2','3','4','5','6'], true))
    {
        filelog("Uživatel $userName se pokusil změnit vzhled stránek, ale zvolil neplatnou možnost.");
        echo "swal('Neplatná možnost.','','error')";
        die();
    }
    
    //Ochrana před SQL injekcí
    $userId = mysqli_real_escape_string($connection, $userId);
    
    //Ukládání změny
    $query = "UPDATE uzivatele SET vzhled = $themeId WHERE uzivatele_id = $userId";
    $result = mysqli_query($connection, $query);
    if (!$result)
    {
        echo "location.href = errSql.html";
        die();
    }
    
    //Aktualizace hodnoty v $_SESSION
    $_SESSION['user']['theme'] = $themeId;
    
    filelog("Uživatel $userName změnil svůj vzhled stránek na vzhled číslo $themeId");
    echo "location.reload();";
