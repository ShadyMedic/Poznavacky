<?php
namespace Poznavacky\Models;

/**
 * Třída obsahující vlastnosti a metody pro různé chybové, varovné, informativní či úspěchové hlášky zobrazované uživateli
 * @author Jan Štěch
 */
class MessageBox
{
    public const MESSAGE_TYPE_SUCCESS = 0;
    public const MESSAGE_TYPE_INFO = 1;
    public const MESSAGE_TYPE_WARNING = 2;
    public const MESSAGE_TYPE_ERROR = 3;
    private const CSS_CLASSES = array(0 => 'success-message', 1 => 'info-message', 2 => 'warning-message', 3 => 'error-message');
    
    private string $message;
    private string $cssClass;

    /**
     * Konstruktor hlášky nastavující její typ a obsah
     * @param int $type Typ hlášky (musí být hodnota jedné z konstatn této třídy začínající na MESSAGE_TYPE)
     * @param string $message Obsah hlášky
     */
    public function __construct(int $type, string $message)
    {
        $this->message = $message;
        $this->cssClass = self::CSS_CLASSES[$type];
    }
    
    /**
     * Metoda navracející asociativní pole s vlastností a CSS třídou pro zobrazení hlášky
     * @return string[] Asociativní pole se dvěma indexy - 'message' a 'messageClass' s obsahem hlášky, respektive CSS třídou pro zobrazení na stránce
     */
    public function getData(): array
    {
        return array('message' => $this->message, 'messageClass' => $this->cssClass);
    }
}

