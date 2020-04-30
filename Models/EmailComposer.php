<?php
/** 
 * Třída skládající e-maily do předpřipravených šablon a navracející hotová těla e-mailů
 * @author Jan Štěch
 */
class EmailComposer
{
    public const EMAIL_TYPE_EMPTY_LAYOUT = 0;
    public const EMAIL_TYPE_PASSWORD_RECOVERY = 1;
    public const EMAIL_TYPE_NAME_CHANGE_APPROVED = 2;
    public const EMAIL_TYPE_NAME_CHANGE_DECLINED = 3;
    
    private $message;
    
    /**
     * Metoda vyplňující potřebnou e-mailovou šablonu poskytnutými daty a nastavující jí jako vlastnost objektu
     * @param int $emailType Číselné označení požadované e-mailové šablony (viz konstanty této třídy)
     * @param array $data Asociativní pole obsahující proměnné pro doplnění šablon (viz šablony ve složce Views/EmailTemplates pro požadované názvy klíčů)
     * @throws InvalidArgumentException Pokud je specifikován neplatný typ šablony
     */
    public function composeMail($emailType, array $data)
    {
        extract($data);
        switch ($emailType)
        {
            case self::EMAIL_TYPE_EMPTY_LAYOUT:
                $template = file_get_contents('Views/EmailTemplates/emptyLayout.phtml');
                $template = str_replace('<?= $content ?>', $content, $template);
                $template = str_replace('<?= $footer ?>', $footer, $template);
                break;
            case self::EMAIL_TYPE_PASSWORD_RECOVERY:
                $template = file_get_contents('Views/EmailTemplates/passwordRecovery.phtml');
                $template = str_replace('<?= $recoveryLink ?>', $recoveryLink, $template);
                break;
            case self::EMAIL_TYPE_NAME_CHANGE_APPROVED:
                $template = file_get_contents('Views/EmailTemplates/nameChangeApproved.phtml');
                $template = str_replace('<?= $websiteAddress ?>', $websiteAddress, $template);
                $template = str_replace('<?= $oldName ?>', $oldName, $template);
                $template = str_replace('<?= $newName ?>', $newName, $template);
                break;
            case self::EMAIL_TYPE_NAME_CHANGE_DECLINED:
                $template = file_get_contents('Views/EmailTemplates/nameChangeDeclined.phtml');
                $template = str_replace('<?= $websiteAddress ?>', $websiteAddress, $template);
                $template = str_replace('<?= $oldName ?>', $oldName, $template);
                $template = str_replace('<?= $declineReason ?>', $declineReason, $template);
                break;
            default:
                throw new InvalidArgumentException('Neznámá e-mailová šablona');
        }
        $this->message = $template;
    }
    
    /**
     * Metoda navracející poskládanou e-mailovou zprávu
     * @return mixed
     */
    public function getMail()
    {
        return $this->message;
    }
    
    /**
     * Metoda odstraňující obsah vlastnosti objektu s poskládanou e-mailovou zprávou
     */
    public function clearMail()
    {
        unset($this->message);
    }
}

