<?php
   function createCssVariable($name, $value)
    {
        echo "--$name: $value; ";
    }
    
    echo ":root{";
    
    $themeId = @$_SESSION['user']['theme'];    //Session nastartováno ve verification.php
    $themeId = (int)$themeId;
    
    $xmlData = simplexml_load_file('themes.xml') or die('</style><script>alert("Chyba při načítání vzhledu.\nVypadá to, že se někde stala chyba při načítání vámi nastaveného vzhledu stránek.\nZkuste to prosím později a pokud tato chyba přetrvá, kontaktujte prosím podporu.");</script></head><body></body></html>');
    try
    {
        if (!in_array($themeId, [0,1,2,3,4,5], true)){throw new Exception();}
        createCssVariable('hlbarva1', $xmlData->theme[$themeId]->color[0]);
        createCssVariable('hlbarva2', $xmlData->theme[$themeId]->color[1]);
        createCssVariable('hlbarva3', $xmlData->theme[$themeId]->color[2]);
        
        echo "}";
        
        //Nastavení obrázku pozadí
        echo "body{background-image: ".$xmlData->theme[$themeId]->picture.";}";
    }
    catch(Exception $e)
    {
        echo '}</style><script>alert("Chyba při načítání vzhledu.\nVypadá to, že se někde stala chyba při načítání vámi nastaveného vzhledu stránek.\nZkuste se prosím odhlásit a přihlásit a pokud tato chyba přetrvá, kontaktujte prosím podporu.\nNyní se načte základní vzhled.");</script><style>:root{';
        require 'php/included/CONSTANTS.php';
        createCssVariable('hlbarva1', $xmlData->theme[DEFAULT_THEME]->color[0]);
        createCssVariable('hlbarva2', $xmlData->theme[DEFAULT_THEME]->color[1]);
        createCssVariable('hlbarva3', $xmlData->theme[DEFAULT_THEME]->color[2]);
        
        echo "}";
        
        //Nastavení obrázku pozadí
        echo "body {background-image: ".$xmlData->theme[DEFAULT_THEME]->picture.";}";
    }