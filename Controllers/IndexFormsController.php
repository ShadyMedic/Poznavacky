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
                    //TODO
                    break;
                //Obnova hesla
                case 'p':
                    //TODO
                    break;
            }
        }
        catch (AccessDeniedException $e)
        {
            setcookie('errorMessage', $e->getMessage());
            setcookie('errorForm', $e->getAdditionalInfo('form'));
            $this->redirect('');
        }
    }
}