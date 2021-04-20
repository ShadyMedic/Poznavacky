<?php
namespace Poznavacky\Models\Emails;

use \InvalidArgumentException;

/**
 * Třída skládající e-maily do předpřipravených šablon a navracející hotová těla e-mailů
 * @author Jan Štěch
 */
class EmailComposer
{
    public const EMAIL_TYPE_EMPTY_LAYOUT = 0;
    public const EMAIL_TYPE_PASSWORD_RECOVERY = 1;
    public const EMAIL_TYPE_USER_NAME_CHANGE_APPROVED = 2;
    public const EMAIL_TYPE_USER_NAME_CHANGE_DECLINED = 3;
    public const EMAIL_TYPE_CLASS_NAME_CHANGE_APPROVED = 4;
    public const EMAIL_TYPE_CLASS_NAME_CHANGE_DECLINED = 5;
    public const EMAIL_TYPE_NEW_CLASS_REQUEST = 6;
    
    private string $message;
    
    /**
     * Metoda vyplňující potřebnou e-mailovou šablonu poskytnutými daty a nastavující jí jako vlastnost objektu
     * @param int $emailType Číselné označení požadované e-mailové šablony (viz konstanty této třídy)
     * @param array $data Asociativní pole obsahující proměnné pro doplnění šablon (viz šablony ve složce
     *     Views/EmailTemplates pro požadované názvy klíčů)
     * @throws InvalidArgumentException Pokud je specifikován neplatný typ šablony
     */
    public function composeMail(int $emailType, array $data): void
    {
        extract($data);
        ob_start();
        switch ($emailType) {
            case self::EMAIL_TYPE_EMPTY_LAYOUT:
                require 'Views/EmailTemplates/emptyLayout.phtml';
                break;
            case self::EMAIL_TYPE_PASSWORD_RECOVERY:
                require 'Views/EmailTemplates/passwordRecovery.phtml';
                break;
            case self::EMAIL_TYPE_USER_NAME_CHANGE_APPROVED:
                require 'Views/EmailTemplates/usernameChangeApproved.phtml';
                break;
            case self::EMAIL_TYPE_USER_NAME_CHANGE_DECLINED:
                require 'Views/EmailTemplates/usernameChangeDeclined.phtml';
                break;
            case self::EMAIL_TYPE_CLASS_NAME_CHANGE_APPROVED:
                require 'Views/EmailTemplates/classnameChangeApproved.phtml';
                break;
            case self::EMAIL_TYPE_CLASS_NAME_CHANGE_DECLINED:
                require 'Views/EmailTemplates/classnameChangeDeclined.phtml';
                break;
            case self::EMAIL_TYPE_NEW_CLASS_REQUEST:
                require 'Views/EmailTemplates/newClassRequest.phtml';
                break;
            default:
                ob_end_clean();
                throw new InvalidArgumentException('Neznámá e-mailová šablona');
        }
        $this->message = ob_get_contents();
        ob_end_clean();
    }
    
    /**
     * Metoda navracející poskládanou e-mailovou zprávu
     * @return string Tělo poskládané e-mailové zprávy
     */
    public function getMail(): string
    {
        return $this->message;
    }
}

