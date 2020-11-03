<?php
/**
 * Kontroler zpracovávající data odeslaná ze stránky manage
 * @author Jan Štěch
 */
class ClassUpdateController extends Controller
{
    /**
     * Metoda odlišující, jakou akci si přeje správce třídy provést a volající příslušný model
     * @see Controller::process()
     */
    public function process(array $parameters): void
    {
        if (empty($_POST))
        {
            header('HTTP/1.0 400 Bad Request');
            exit();
        }
        
        header('Content-Type: application/json');
        //Kontrola, zda je zvolena nějaká třída
        if (!isset($_SESSION['selection']['class']))
        {
            echo json_encode(array('messageType' => 'error', 'message' => AccessDeniedException::REASON_CLASS_NOT_CHOSEN, 'origin' => $_POST['action']));
            exit();
        }
        $class = $_SESSION['selection']['class'];
        
        //Kontrola, zda je nějaký uživatel přihlášen a zda je přihlášený uživatel správcem vybrané třídy
        $aChecker = new AccessChecker();
        if (!$aChecker->checkUser() || !$class->checkAdmin(UserManager::getId()))
        {
            header('HTTP/1.0 403 Forbidden');
            exit();
        }
        
        try
        {
            switch ($_POST['action'])
            {
                case 'request name change':
                    $newName =$_POST['newName'];
                    $class->requestNameChange($newName);
                    echo json_encode(array('messageType' => 'success', 'message' => 'Žádost o změnu názvu třídy byla odeslána. Sledujte prosím svou e-mailovou schránku (pokud jste si zde nastavili e-mailovou adresu). V okamžiku, kdy vaši žádost posoudí správce, dostanete zprávu.'));
                    break;
                case 'update access':
                    $newStatus = @ClassObject::CLASS_STATUSES_DICTIONARY[mb_strtolower($_POST['newStatus'])];
                    if (empty($newStatus)){ $newStatus = 'unknown'; }
                    $newCode = $_POST['newCode'];
                    $class->updateAccessData($newStatus, $newCode);
                    echo json_encode(array('messageType' => 'success', 'message' => 'Přístupová data třídy byla úspěšně změněna'));
                    break;
                case 'kick member':
                    $kickedUserId = $_POST['memberId'];
                    $class->removeMember($kickedUserId);
                    echo json_encode(array('messageType' => 'success', 'message' => 'Uživatel byl úspěšně odebrán ze třídy'));
                    break;
                case 'invite user':
                    $invitedUserName = $_POST['userName'];
                    $class->inviteUser($invitedUserName);
                    echo json_encode(array('messageType' => 'success', 'message' => 'Pozvánka úspěšně odeslána'));
                    break;
                case 'create test':
                    $adder = new GroupAdder($class);
                    $adder->processFormData($_POST);
                    $this->addMessage(MessageBox::MESSAGE_TYPE_SUCCESS, 'Poznávačka '.$_POST['testName'].' úspěšně vytvořena');
                    echo json_encode(array('messageType' => 'success'));
                    break;
                case 'delete test':
                    $deletedTestId = $_POST['testId'];
                    $test = new Group(false, $deletedTestId);
                    try { $class->removeGroup($test); } catch (BadMethodCallException $e) { throw new NoDataException(NoDataException::UNKNOWN_GROUP); }
                    echo json_encode(array('messageType' => 'success', 'message' => 'Poznávačka byla odstraněna'));
                    break;
                case 'delete class':
                    $adminPassword = $_POST['password'];
                    $class->deleteAsClassAdmin($adminPassword);
                    $this->addMessage(MessageBox::MESSAGE_TYPE_SUCCESS, 'Třída byla odstraněna');
                    echo json_encode(array('messageType' => 'success'));
                    break;
                case 'verify password':
                    $password = urldecode($_POST['password']);
                    if (mb_strlen($password) === 0)
                    {
                        throw new AccessDeniedException(AccessDeniedException::REASON_NO_PASSWORD_GENERAL);
                    }
                    if (!$aChecker->recheckPassword($password))
                    {
                        throw new AccessDeniedException(AccessDeniedException::REASON_WRONG_PASSWORD_GENERAL);
                    }
                    echo json_encode(array('verified' => true));
                    break;
                default:
                    header('HTTP/1.0 400 Bad Request');
                    exit();
            }
        }
        catch (AccessDeniedException | NoDataException $e)
        {
            echo json_encode(array('messageType' => 'error', 'message' => $e->getMessage(), 'origin' => $_POST['action']));
        }
        
        //Zastav zpracování PHP, aby se nevypsala šablona
        exit();
    }
}