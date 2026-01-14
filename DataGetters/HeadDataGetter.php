<?php
namespace Poznavacky\DataGetters;

use Poznavacky\Models\DatabaseItems\User;
use Poznavacky\Models\Security\AccessChecker;
use Poznavacky\Models\Statics\Settings;
use Poznavacky\Models\Statics\UserManager;

class HeadDataGetter implements DataGetter
{
    
    /**
     * @inheritDoc
     */
    public function get(): array
    {
        $result = array();
        $result['theme'] = ((new AccessChecker())->checkUser()) ? (UserManager::getUser()->offsetGet('theme') ?? Settings::DEFAULT_THEME) : Settings::DEFAULT_THEME;
        $result['messages'] = $this->getMessages();
        $result['currentYear'] = date('Y');
        return $result;
    }
    
    /**
     * Metoda načítající hlášky pro uživatele uložené v $_SESSION a přidávající jejich obsah do dat, které jsou později
     * předány pohledu Hlášky jsou poté ze sezení vymazány
     */
    private function getMessages(): array
    {
        if (isset($_SESSION['messages'])) {
            $messages = $_SESSION['messages'];
            $messagesData = array();
            foreach ($messages as $messageBox) {
                $messagesData[] = $messageBox->getData();
            }
            $this->clearMessages();
            return $messagesData;
        } else {
            return array();
        }
    }
    
    /**
     * Metoda odstraňující všechny hlášky pro uživatele uloženy v $_SESSION
     */
    private function clearMessages(): void
    {
        unset($_SESSION['messages']);
    }
}

