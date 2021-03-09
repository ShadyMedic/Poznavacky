<?php
namespace Poznavacky\DataGetters;

use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Security\AccessChecker;

class MenuDataGetter implements DataGetter
{

    /**
     * @inheritDoc
     */
    public function get(): array
    {
        $result = array();
        $aChecker = new AccessChecker();
        try
        {
            $result['adminLogged'] = $aChecker->checkSystemAdmin();
            $result['demoVersion'] = $aChecker->checkDemoAccount();
        }
        catch (AccessDeniedException $e)
        {
            //Žádný uživatel není přihlášen (to by se kvůli rootování teoreticky nemohlo stát)
            $result['adminLogged'] = false;
            $result['demoVersion'] = true;
        }

        return $result;
    }
}

