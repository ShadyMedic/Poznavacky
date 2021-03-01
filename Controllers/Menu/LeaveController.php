<?php
namespace Poznavacky\Controllers\Menu;

use Poznavacky\Controllers\SynchronousController;
use Poznavacky\Models\Logger;
use Poznavacky\Models\Statics\UserManager;
use Poznavacky\Models\MessageBox;

/**
 * Kontroler starající se o opuštění třídy
 * @author Jan Štěch
 */
class LeaveController extends SynchronousController
{
    /**
     * Metoda odstraňujícího přihlášeného uživatele ze zvolené třídy (ruší jeho členství)
     * @param array $parameters Parametry pro zpracování kontrolerem (nevyužíváno)
     * @see SynchronousController::process()
     */
    public function process(array $parameters): void
    {
        $userId = UserManager::getId();
        $class = $_SESSION['selection']['class'];
        $classId = $class->getId();
        if ($class->checkAdmin($userId))
        {
            //Správce třídy jí nemůže opustit
            (new Logger(true))->notice('Uživatel s ID {userId} se pokusil opustit třídu s ID {classId} z IP adresy {ip}, avšak jelikož je její správce, nebylo mu toto umožněno', array('userId' => $userId, 'classId' => $classId, 'ip' => $_SERVER['REMOTE_ADDR']));
            $this->addMessage(MessageBox::MESSAGE_TYPE_ERROR, 'Jako správce třídy nemůžete třídu opustit');
            $this->redirect('menu');
        }
        $class->removeMember($userId);
        (new Logger(true))->info('Uživatel s ID {userId} opustil třídu s ID {classId} z IP adresy {ip}', array('userId' => $userId, 'classId' => $classId, 'ip' => $_SERVER['REMOTE_ADDR']));
        $this->addMessage(MessageBox::MESSAGE_TYPE_SUCCESS, 'Třída úspěšně opuštěna');
        $this->redirect('menu');
    }
}

