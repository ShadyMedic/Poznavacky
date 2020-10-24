<?php
/**
 * Kontroler starající se o opuštění třídy
 * @author Jan Štěch
 */
class LeaveController extends Controller
{
    /**
     * Metoda odstraňujícího přihlášeného uživatele ze zvolené třídy (ruší jeho členství)
     * @see Controller::process()
     */
    public function process(array $parameters)
    {
        $userId = UserManager::getId();
        $class = $_SESSION['selection']['class'];
        if ($class->checkAdmin($userId))
        {
            //Správce třídy jí nemůže opustit
            $this->addMessage(MessageBox::MESSAGE_TYPE_ERROR, 'Jako správce třídy nemůžete třídu opustit');
            $this->redirect('menu');
        }
        $class->removeMember($userId);
        $this->addMessage(MessageBox::MESSAGE_TYPE_SUCCESS, 'Třída úspěšně opuštěna');
        $this->redirect('menu');
    }
}