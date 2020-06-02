<?php
/**
 * Kontroler zpracovávající data z formuláře pro zadání kódu od soukromé třídy na menu stránce
 * @author Jan Štěch
 */
class EnterClassCodeController extends Controller
{
    /**
     * Metoda zpracovávající data odeslaná formulářem
     * @see Controller::process()
     */
    public function process(array $parameters)
    {
        if (!isset($_POST) || !isset($_POST['code']))
        {
            //Chybně vyplněný formulář
            $this->addMessage(MessageBox::MESSAGE_TYPE_ERROR, 'Musíte vyplnit kód třídy');
            $this->redirect('menu');
        }
        
        $code = $_POST['code'];
        $userId = UserManager::getId();
        
        //Validace kódu
        $validator = new DataValidator();
        if (!$validator->validateClassCode($code))
        {
            $this->addMessage(MessageBox::MESSAGE_TYPE_ERROR, 'Vstupní kód třídy musí být tvořen čtyřmi číslicemi');
            $this->redirect('menu');
        }
        
        $classes = ClassManager::getNewClassesByAccessCode($code, $userId);
        if (!$classes)
        {
            //Se zadaným kódem se nelze dostat do žádné třídy
            $this->addMessage(MessageBox::MESSAGE_TYPE_ERROR, 'Zadaný kód není platný');
            $this->redirect('menu');
        }
        
        $accessedClasses = array();
        foreach($classes as $class)
        {
            if ($class->addMember($userId))
            {
                $accessedClasses[] = $class->getName();
            }
        }
        
        if (count($accessedClasses) > 0)
        {
            //Vypsat do zprávy pro uživatele jména tříd do kterých získal přístup uložená v $accessedClasses
            $this->addMessage(MessageBox::MESSAGE_TYPE_SUCCESS, 'Získali jste přístup do následujících tříd: '.implode(', ',$accessedClasses));
        }
        else
        {
            $this->addMessage(MessageBox::MESSAGE_TYPE_ERROR, 'Žádné třídy s tímto přístupovým kódem nebyly nalezeny');
        }
        
        $this->redirect('menu');
    }
}

