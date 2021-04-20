<?php
namespace Poznavacky\Models;

/**
 * Třída, s jejíž pomocí se odesílá formálně sjednocená odpověď na všechny AJAX požadavky
 * @author Jan Štěch
 */
class AjaxResponse
{
    public const MESSAGE_TYPE_REDIRECT = 'redirect';    //Při změně hodnoty této konstanty je nutné změnit řetězec i v souboru js/ajaxMediator.js !!!
    public const MESSAGE_TYPE_SUCCESS = 'success';      //Při změně hodnot následujících čtyř konstant je potřeba změnit hodnoty i ve všech JS souborech zpracovávajících odpověď na AJAX požadavky !!!
    public const MESSAGE_TYPE_INFO = 'info';
    public const MESSAGE_TYPE_WARNING = 'warning';
    public const MESSAGE_TYPE_ERROR = 'error';
    
    //Při změně následujících tří konstant je nutné aktualizovat řetězce i v souboru js/ajaxMediator.js !!!
    private const MESSAGE_TYPE_KEY = 'messageType';
    private const MESSAGE_KEY = 'message';
    private const MESSAGE_DATA_KEY = 'data';
    
    private string $messageType;
    private string $message;
    private array $data;
    
    /**
     * Konstruktor objektu pro odpověď na AJAX request nastavující všechny jeho vlastnosti
     * @param string $result Typ zprávy, musí být jedna z veřejných konstant této třídy, jediný povinný argument
     * @param string $message Zpráva
     * @param array $data Pole dalších dat pro odeslání
     */
    public function __construct(string $result, string $message = '', array $data = array())
    {
        $this->messageType = $result;
        $this->message = $message;
        $this->data = $data;
    }
    
    /**
     * Metoda kódující data pro odpověď do JSON stringu
     * @return string JSON string pro odeslání zpět klientovi
     */
    public function getResponseString(): string
    {
        $res = array();
        $res[self::MESSAGE_TYPE_KEY] = $this->messageType;
        $res[self::MESSAGE_KEY] = $this->message;
        if (!empty($this->data)) {
            $res[self::MESSAGE_DATA_KEY] = $this->data;
        }
        
        return json_encode($res);
    }
}

