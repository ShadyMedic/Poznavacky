<?php

/** 
 * Kontroler zpracovávající data z formulářů na index stránce
 * (přihlášení, registrace, obnova hesla)
 * @author Jan Štěch
 */
class IndexFormsController extends Controller
{
    /**
     * Metoda přijímající data z formulářů skrz $_POST a volající model, který je zpracuje.
     * Podle výsledku zpracování dat přesměrovává na menu stránku nebo zobrazuje chybovou hlášku.
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
                    LoginUser::processLogin($_POST);
                    $this->redirect('menu');
                    break;
                //Registrace
                case 'r':
                    RegisterUser::processRegister($_POST);
                    $this->redirect('menu');
                    break;
                //Obnova hesla
                case 'p':
                    if (RecoverPassword::processRecovery($_POST))
                    {
                        $_SESSION['success']['form'] = 'passRecovery';
                        $_SESSION['success']['message'] = 'Na vámi zadanou e-mailovou adresu byly odeslány další instrukce pro obnovu hesla. Pokud vám e-mail nepřišel, zkontrolujte prosím i složku se spamem a/nebo opakujte akci. V případě dlouhodobých problému prosíme kontaktujte správce.';
                    }
                    $this->redirect('');
                    break;
            }
        }
        catch (AccessDeniedException $e)
        {
            $_SESSION['error']['form'] = $e->getAdditionalInfo('form');
            $_SESSION['error']['message'] = $e->getMessage();
            $_SESSION['previousAnswers'] = serialize($_POST);
            $this->redirect('');
        }
    }
}