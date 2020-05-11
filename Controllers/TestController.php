<?php
/** 
 * Kontroler starající se o výpis stránky pro testování
 * @author Jan Štěch
 */
class TestController extends Controller
{

    /**
     * Metoda ověřující, zda má uživatel do třídy přístup a nastavující hlavičku stránky a pohled
     * @see Controller::process()
     */
    public function process(array $parameters)
    {
        $class = new ClassObject(0, $parameters[0]);
        if (!$class->checkAccess(UserManager::getId()))
        {
            $this->redirect('error403');
        }
        
        $this->pageHeader['title'] = 'Testovat';
        $this->pageHeader['description'] = 'Vyzkoušejte si, jak dobře znáte přírodniny v poznávačce pomocí náhodného testování';
        $this->pageHeader['keywords'] = '';
        $this->pageHeader['cssFile'] = 'css/css.css';
        $this->pageHeader['jsFile'] = 'js/test.js';
        $this->pageHeader['bodyId'] = 'test';
        
        $this->view = 'test';
    }
}