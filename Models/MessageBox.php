<?php
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
    private const CSS_CLASSES = array(0 => 'successMessage', 1 => 'infoMessage', 2 => 'warningMessage', 3 => 'errorMessage');
    
    private $message;
    private $cssClass;

    /**
     */
    public function __construct(int $type, string $message)
    {
        $this->message = $message;
        $this->cssClass = self::CSS_CLASSES[$type];
    }
    
    public function getData()
    {
        return array('message' => $this->message, 'messageClass' => $this->cssClass);
    }
}