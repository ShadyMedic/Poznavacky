<?php
/** 
 * Kontroler zpracovávající data z formulářů na index stránce
 * (přihlášení, registrace, obnova hesla)
 * Kontroler je volán pomocí AJAX požadavku z index.js
 * @author Jan Štěch
 */
class IndexFormsController extends Controller
{
    /**
     * Metoda přijímající data z formulářů skrz $_POST a volající model, který je zpracuje.
     * Podle výsledku zpracování dat odesílá instrukce k přesměrování na menu stránku nebo odesílá chybovou hlášku.
     * @see Controller::process()
     */
    public function process(array $paremeters)
    {
        try
        {
            $type = $_POST['type'];
            switch($type)
            {
                //Přihlašování
                case 'l':
                    $form = 'login';
                    LoginUser::processLogin($_POST);
                    echo json_encode(array('redirect' => 'menu'));
                    break;
                //Registrace
                case 'r':
                    $form = 'register';
                    RegisterUser::processRegister($_POST);
                    echo json_encode(array('redirect' => 'menu'));
                    break;
                //Obnova hesla
                case 'p':
                    $form = 'passRecovery';
                    if (RecoverPassword::processRecovery($_POST))
                    {
                        header('HTTP/1.0 401 Unauthorized');
                        echo json_encode(array('messageType' => 'success', 'message' => 'Na vámi zadanou e-mailovou adresu byly odeslány další instrukce pro obnovu hesla. Pokud vám e-mail nepřišel, zkontrolujte prosím i složku se spamem a/nebo opakujte akci. V případě dlouhodobých problémů prosíme kontaktujte správce.', 'origin' => $form));
                    }
                    break;
            }
        }
        catch (AccessDeniedException $e)
        {
            echo json_encode(array('messageType' => 'error', 'message' => $e->getMessage(), 'origin' => $form));
        }
        
        //Zastav zpracování PHP, aby se nevypsala šablona
        exit();
    }
}