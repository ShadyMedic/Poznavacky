<?php
    /**
     * $type: určuje typ e-mailu
     *      0: obnova hesla
     *      1: potvrzení změny jména
     *      2: zamítnutí změny jména
     */
    function getEmail($type, $data)
    {
        $email = "";
        
        //Hlavička
        $email .= "
        <table width='100%' cellpadding='0' border='0' cellspacing='0'>
            <tbody>
                <tr>
                    <td align='center' bgcolor='#eeeeee'>
                        <table width='660' cellpadding='0' border='0' cellspacing='0' align='center' bgcolor='#FFFFFF' style='font-size:15px;font-family:Helvetica,Arial,sans-serif;line-height:25px;color:#445566;border-top: 10px solid #405d27;'>
                            <tbody>
                                <tr>
                                    <td align='left' style='padding:45px 50px 45px 50px'>
                                        <div>
        ";
        
        //Hlavní text
        if ($type === 0)
        {
            $email .= "
                <p>Pro obnovení vašeho hesla klikněte na tento odkaz: <a href='localhost/Poznavacky/emailPasswordRecovery.php?token=".$data['code']."'>OBNOVIT HESLO</a></p>
                <p>Tento odkaz bude platný po následujících 24 hodin, nebo do odeslání žádosti o nový kód.</p>
                <p style='color: #990000;'><span style='font-weight: bold;'>DŮLEŽITÉ: </span><span>Tento e-mail nikomu nepřeposílejte! Mohl by získat přístup k vašemu účtu.</span>
            ";
        }
        if ($type === 1)
        {
            $email .= "
                <p>Na základě vaší žádosti na <a href='poznavacky.chytrak.cz'>poznavacky.chytrak.cz</a> bylo změněno vaše uživatelské jméno na <b>".$data['newName']."</b>.
                <br>Pod svým starým jménem (<b>".$data['oldName']."</b>) se od nynějška již nebudete moci přihlásit.</p>
                <p>Pokud si přejete změnit jméno zpět na staré nebo nějaké úplně jiné, můžete tak učinit odesláním další žádosti o změnu jména v nastavení vašeho uživatelského účtu.</p>
                <p>Neodesílali jste žádnou žádost na změnu uživatelského jména? Je možné, že někdo získal přístup k vašemu účtu. Doporučujeme vám si co nejdříve změnit vaše heslo. Pokud se nemůžete přihlásit, kontaktujte nás prosím na e-mailové adrese <a href='mailto:poznavacky@email.com'>poznavacky@email.com</a>".
                "</p>
            ";
        }
        if ($type === 2)
        {
            $email .= "
            <p>Vaše žádost o změnu uživatelského jména na <a href='poznavacky.chytrak.cz'>poznavacky.chytrak.cz</a> byla administrátorem zamítnuta.
            <br><b>Důvod zamítnutí: <span style='color:#990000'>".$data['reason']."</span>.</b><br>
            Vaše současné jméno (<b>".$data['oldName']."</b>) tak stále zůstává platným přihlašovacím údajem.</p>
            <p>Pokud si jméno stále chcete změnit, můžete odeslat novou žádost o změnu. Neodesílejte však prosím žádost o změnu na jméno, které bylo zamítnuto.</p>
            <p>Neodesílali jste žádnou žádost na změnu uživatelského jména? Je možné, že někdo získal přístup k vašemu účtu. Doporučujeme vám si co nejdříve změnit vaše heslo. Pokud se nemůžete přihlásit, kontaktujte nás prosím na e-mailové adrese <a href='mailto:poznavacky@email.com'>poznavacky@email.com</a>.</p>
            ";
        }
        
        //Patička
        $email .= "
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <table width='660' cellpadding='0' border='0' cellspacing='0' align='center' style='font-family:Arial,Helvetica,sans-serif;color:#666666;font-size:12px;line-height:18px;text-align:center;'>
                            <tbody>
                                <tr>
                                    <td>
                                        <div>
                                            <p style='margin-top:30px;margin-bottom:30px'>
        ";
        
        //Zápatí
        if ($type === 0)
        {
            $email .= "<p>Pokud jste o obnovu hesla nezažádali, můžete tento e-mail ignorovat.</p>";
            $email .= "<p>V případě problémů nás kontaktujte na <a href='mailto:poznavacky@email.com'>poznavacky@email.com</a></p>";
        }
        $email .= "<p>Toto je automaticky vygenerovaná zpráva. Prosíme, neodpovídejte na ni.</p>";
        
        //Zbytek patičky
        $email .= "
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
        ";
        
        return $email;
    }