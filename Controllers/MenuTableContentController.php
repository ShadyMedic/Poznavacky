<?php
/** 
 * Kontroler starající se o výpis tabulky a jejího obsahu do menu stránky
 * @author Jan Štěch
 */
class MenuTableContentController extends Controller
{
    private $aquiredData;
    
    public function __construct(string $viewWithTable, $data)
    {
        $this->view = $viewWithTable;
        $this->aquiredData = $data;
    }
    
    public function process(array $parameters): void
    {
        if (gettype($this->aquiredData) === 'string')
        {
            //Vypisujeme prostou textovou hlášku
            $this->data['message'] = $this->aquiredData;
            return;
        }
        
        $this->data['table'] = $this->aquiredData;
    }
}

