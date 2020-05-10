<?php
/**
 * Třída generující slovní zápis náhodně zvoleného čísla v rozmezí <0;99>
 * Uživatel musí číslo přepsat do slovní podoby
 * @author Jan Štěch
 */
class NumberAsWordCaptcha extends Captcha
{
    public const SESSION_INDEX = 'numberAsWordCaptchaAnswer';
    
    /**
     * Metoda generující novou náhodnou captchu
     * Otázka i odpověď jsou uloženy jako vlastnosti objektu a odpověď je uložena i do $_SESSION
     * @see Captcha::generate()
     */
    public function generate()
    {
        $digits = explode(',','nula,jedna,dva,tři,čtyři,pět,šest,sedm,osm,devět');
        $tens = explode(',', 'nula,deset,dvacet,třicet,čtyřicet,padesát,šedesát,sedmdesát,osmdesát,devadesát');
        
        $number = rand(0,99);
        
        if ($number <= 9){$word = $digits[$number];}
        else if ($number % 10 === 0){$word = $tens[$number / 10];}
        else if ($number <= 19)
        {
            switch ($number)
            {
                case 11:
                    $word = "jedenáct";
                    break;
                case 12:
                    $word = "dvanáct";
                    break;
                case 13:
                    $word = "třináct";
                    break;
                case 14:
                    $word = "čtrnáct";
                    break;
                case 15:
                    $word = "patnáct";
                    break;
                case 16:
                    $word = "šestnáct";
                    break;
                case 17:
                    $word = "sedmnáct";
                    break;
                case 18:
                    $word = "osmnáct";
                    break;
                case 19:
                    $word = "devatenáct";
                    break;
            }
        }
        else {$word = $tens[floor($number / 10)].' '.$digits[$number % 10];}
        
        $this->question = $word;
        $this->answer = $number;
        $this->setAnswer(self::SESSION_INDEX);
    }
}

