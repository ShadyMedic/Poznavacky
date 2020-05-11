<?php
/**
 * Kontroler starající se o opuštění třídy
 * @author Jan Štěch
 */
class LeaveController extends Controller
{
    /**
     * (non-PHPdoc)
     *
     * @see Controller::process()
     */
    public function process(array $parameters)
    {
        $userId = UserManager::getId();
        $class = new ClassObject(0, urldecode($parameters[0]));
        if ($class->checkAdmin($userId))
        {
            //Správce třídy jí nemůže opustit
            $this->addMessage(MessageBox::MESSAGE_TYPE_ERROR, 'Jako správce třídy nemůžete třídu opustit');
        }
        $class->removeMember($userId);
        $this->addMessage(MessageBox::MESSAGE_TYPE_SUCCESS, 'Třída úspěšně opuštěna');
        $this->redirect('menu');
    }
}