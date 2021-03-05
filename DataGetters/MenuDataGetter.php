<?php
namespace Poznavacky\DataGetters;

use Poznavacky\DataGetters\DataGetter;
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
        $result['adminLogged'] = $aChecker->checkSystemAdmin();
        $result['demoVersion'] = $aChecker->checkDemoAccount();
        return $result;
    }
}

