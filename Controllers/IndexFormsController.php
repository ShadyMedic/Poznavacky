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
        header('Content-Type: application/json');
        try
        {
            $type = $_POST['type'];
            switch($type)
            {
                //Přihlašování
                case 'l':
                    $form = 'login';
                    $userLogger = new LoginUser();
                    $userLogger->processLogin($_POST);
                    echo json_encode(array('redirect' => 'menu'));
                    break;
                //Registrace
                case 'r':
                    $form = 'register';
                    $userRegister = RegisterUser();
                    $userRegister->processRegister($_POST);
                    echo json_encode(array('redirect' => 'menu'));
                    break;
                //Obnova hesla
                case 'p':
                    $form = 'passRecovery';
                    $passwordRecoverer = new RecoverPassword();
                    if ($passwordRecoverer->processRecovery($_POST))
                    {
                        echo json_encode(array('messageType' => 'success', 'message' => 'Na vámi zadanou e-mailovou adresu byly odeslány další instrukce pro obnovu hesla. Pokud vám e-mail nepřišel, zkontrolujte prosím i složku se spamem a/nebo opakujte akci. V případě dlouhodobých problémů prosíme kontaktujte správce.', 'origin' => $form));
                    }
                    else
                    {
                        echo json_encode(array('messageType' => 'error', 'message' => 'E-mail pro obnovu hesla se nepovedlo odeslat. Kontaktujte prosím administrátora, nebo zkuste akci opakovat později.'));
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