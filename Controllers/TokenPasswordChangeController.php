<?php
/**
 * Kontroler starající se o zpracování dat odeslaných z formuláře pro obnovení hesla
 * @author Jan Štěch
 */
class TokenPasswordChangeController extends Controller
{

    /**
     * Kontroler přijímající data odeslaná formulářem a volající modely, které je zpracují.
     * Také nastavuje úspěchovou nebo chybové hlášky a přesměrovává zpět nebo nastavuje pohled pro zobrazení zprávy.
     * @see Controller::process()
     */
    public function process(array $parameters)
    {
        $token = $_POST['token'];
        $pass = $_POST['pass'];
        $repass = $_POST['repass'];
        
        try
        {
            $passwordChanger = new TokenPasswordChanger($token, $pass, $repass);
            $passwordChanger->verifyToken();
            $passwordChanger->checkPasswords();
            $passwordChanger->changePassword();
            $passwordChanger->devalueToken();
        }
        catch (AccessDeniedException $e)
        {
            $_SESSION['recoveryErrorMessage'] = $e->getMessage();
            $this->redirect('recoverPassword/'.$token);
        }
        
        $this->data['message'] = 'Heslo bylo úspěšně změněno';
        $this->pageHeader['bodyId'] = 'recoverPasswordMessage';
        $this->view = 'recoverPasswordMessage';
    }
}

