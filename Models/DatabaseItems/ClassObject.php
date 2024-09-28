<?php

namespace Poznavacky\Models\DatabaseItems;

use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Exceptions\DatabaseException;
use Poznavacky\Models\Security\AccessChecker;
use Poznavacky\Models\Security\DataValidator;
use Poznavacky\Models\Statics\Db;
use Poznavacky\Models\Statics\Settings;
use Poznavacky\Models\Statics\UserManager;
use Poznavacky\Models\Logger;
use Poznavacky\Models\undefined;
use \BadMethodCallException;
use \DateTime;
use \Exception;
use \InvalidArgumentException;
use \RangeException;

/**
 * Třída reprezentující objekt třídy (jakože té z reálného světa / obsahující poznávačky)
 * @author Jan Štěch
 */
class ClassObject extends Folder
{
    public const TABLE_NAME = 'tridy';

    public const COLUMN_DICTIONARY = array(
        'id' => 'tridy_id',
        'name' => 'nazev',
        'url' => 'url',
        'status' => 'status',
        'code' => 'kod',
        'groupsCount' => 'poznavacky',
        'readonly' => 'pouze_pro_cteni',
        'admin' => 'spravce'
    );

    protected const NON_PRIMITIVE_PROPERTIES = array(
        'admin' => User::class
    );

    protected const DEFAULT_VALUES = array(
        'status' => self::CLASS_STATUS_PRIVATE,
        'code' => 0,
        'readonly' => 0,
        'groups' => array(),
        'groupsCount' => 0,
        'members' => array()//,
        //'naturals' => array()
    );

    protected const CAN_BE_CREATED = false;
    protected const CAN_BE_UPDATED = true;

    public const CLASS_STATUS_PUBLIC = "public";
    public const CLASS_STATUS_PRIVATE = "private";
    public const CLASS_STATUS_LOCKED = "locked";

    public const CLASS_STATUSES_DICTIONARY = array(
        'veřejná' => self::CLASS_STATUS_PUBLIC,
        'soukromá' => self::CLASS_STATUS_PRIVATE,
        'uzamčená' => self::CLASS_STATUS_LOCKED
    );

    protected $status;
    protected $code;
    protected $readonly;
    protected $groupsCount;
    protected $admin;

    protected $members;
    protected $groups;
    //protected $naturals; //Nemůže se ukládat, protože při předání objektu třídy pohledu se ošetřuje obrovské množství dat proti XSS

    private $accessCheckResult;

    /**
     * Metoda nastavující všechny vlasnosti objektu (s výjimkou ID) podle zadaných argumentů
     * Při nastavení některého z argumentů na undefined, je hodnota dané vlastnosti také nastavena na undefined
     * Při nastavení některého z argumentů na null, není hodnota dané vlastnosti nijak pozměněna
     * @param string|undefined|null $name Název třídy
     * @param string|undefined|null $url Reprezentace názvu třídy pro použití v URL
     * @param string|undefined|null $status Status přístupnosti třídy (musí být jedna z konstant této třídy začínající
     *     na CLASS_STATUS_)
     * @param int|undefined|null $code Přístupový kód třídy
     * @param bool|undefined|null $readonly Zda mohou do třídy přidávat obrázky i její nečlenové (TRUE, pokud ne)
     * @param Group[]|undefined|null $groups Pole poznávaček patřících do této třídy jako objekty
     * @param int|undefined|null $groupsCount Počet poznávaček patřících do této třídy (při vyplnění parametru $groups
     *     je ignorováno a je použita délka poskytnutého pole)
     * @param User[]|undefined|null $members Pole uživatelů, kteří mají členství v této třídě
     * @param User|undefined|null $admin Odkaz na objekt uživatele, který je správcem této třídy
     * {@inheritDoc}
     * @see DatabaseItem::initialize()
     */
    public function initialize($name = null, $url = null, $status = null, $code = null, $readonly = null, $groups = null,
                               $groupsCount = null, $members = null, $admin = null): void
    {
        //Kontrola nespecifikovaných hodnot (pro zamezení přepsání známých hodnot)
        if ($name === null) {
            $name = $this->name;
        }
        if ($url === null) {
            $url = $this->url;
        }
        if ($status === null) {
            $status = $this->status;
        }
        if ($code === null) {
            $code = $this->code;
        }
        if ($readonly === null) {
            $readonly = $this->readonly;
        }
        if ($groups === null) {
            $groups = $this->groups;
            if ($groupsCount === null) {
                $groupsCount = $this->groupsCount;
            }
        } else {
            $groupsCount = count($groups);
        }
        if ($members === null) {
            $members = $this->members;
        }
        if ($admin === null) {
            $admin = $this->admin;
        }

        $this->name = $name;
        $this->url = $url;
        $this->status = $status;
        $this->code = $code;
        $this->readonly = $readonly;
        $this->groups = $groups;
        $this->groupsCount = $groupsCount;
        $this->members = $members;
        $this->admin = $admin;
    }

    /**
     * Metoda navracející počet poznávaček v této třídě
     * @return int Počet poznávaček
     * @throws DatabaseException
     */
    public function getGroupsCount(): int
    {
        $this->loadIfNotLoaded($this->groupsCount);
        return $this->groupsCount;
    }

    /**
     * Metoda navracející uložený status této třídy.
     * @return string Status třídy (viz konstanty třídy)
     * @throws DatabaseException
     */
    public function getStatus(): string
    {
        $this->loadIfNotLoaded($this->status);
        return $this->status;
    }

    /**
     * Metoda navracející uložený vstupní kód této třídy
     * @return int|null Čtyřmístný kód této třídy nebo NULL, pokud žádný kód není nastaven
     * @throws DatabaseException
     */
    public function getCode(): ?int
    {
        $this->loadIfNotLoaded($this->code);
        return $this->code;
    }

    /**
     * Metoda navracející, zda je třída nastavená pouze pro čtení a zda tak do ní mohou přidávat obrázky pouze členové
     * (čili uživatelé, kteří mají záznam v tabulce "clenstvi" v důsledku pozvání nebo zadání vstupního kódu).
     * @return bool TRUE, pokud je třída nastavena jako pouze pro čtení, FALSE, pokud ne
     */
    public function isReadOnly(): bool
    {
        $this->loadIfNotLoaded($this->readonly);
        return $this->readonly;
    }

    /**
     * Metoda pro získání objektu uživatele, který je správcem této třídy
     * @return User Objekt správce třídy
     * @throws DatabaseException
     */
    public function getAdmin(): User
    {
        $this->loadIfNotLoaded($this->admin);
        return $this->admin;
    }

    /**
     * Metoda získávající hlášení všech obrázků patřících k přírodninám, které jsou součástí této třídy
     * @return Report[] Pole objektů hlášení
     * @throws DatabaseException
     */
    public function getReports(): array
    {
        $this->loadIfNotLoaded($this->id);
        
        //Získání důvodů hlášení vyřizovaných správcem třídy
        $availableReasons = array_diff(Report::ALL_REASONS, Report::ADMIN_REQUIRING_REASONS);
        
        $in = str_repeat('?,', count($availableReasons) - 1).'?';
        $sqlArguments = array_values($availableReasons);
        $sqlArguments[] = $this->id;
        $result = Db::fetchQuery('
            SELECT
            '.Report::TABLE_NAME.'.'.Report::COLUMN_DICTIONARY['id'].' AS "hlaseni_id", '.Report::TABLE_NAME.'.'.
                                 Report::COLUMN_DICTIONARY['reason'].' AS "hlaseni_duvod", '.Report::TABLE_NAME.'.'.
                                 Report::COLUMN_DICTIONARY['additionalInformation'].' AS "hlaseni_dalsi_informace", '.
                                 Report::TABLE_NAME.'.'.Report::COLUMN_DICTIONARY['reportersCount'].' AS "hlaseni_pocet",
            '.Picture::TABLE_NAME.'.'.Picture::COLUMN_DICTIONARY['id'].' AS "obrazky_id", '.Picture::TABLE_NAME.'.'.
                                 Picture::COLUMN_DICTIONARY['src'].' AS "obrazky_zdroj", '.Picture::TABLE_NAME.'.'.
                                 Picture::COLUMN_DICTIONARY['enabled'].' AS "obrazky_povoleno",
            '.Natural::TABLE_NAME.'.'.Natural::COLUMN_DICTIONARY['id'].' AS "prirodniny_id", '.Natural::TABLE_NAME.'.'.
                                 Natural::COLUMN_DICTIONARY['name'].' AS "prirodniny_nazev", '.Natural::TABLE_NAME.'.'.
                                 Natural::COLUMN_DICTIONARY['picturesCount'].' AS "prirodniny_obrazky"
            FROM hlaseni
            JOIN '.Picture::TABLE_NAME.' ON '.Report::TABLE_NAME.'.'.Report::COLUMN_DICTIONARY['picture'].' = '.
                                 Picture::TABLE_NAME.'.'.Picture::COLUMN_DICTIONARY['id'].'
            JOIN '.Natural::TABLE_NAME.' ON '.Picture::TABLE_NAME.'.'.Picture::COLUMN_DICTIONARY['natural'].' = '.
                                 Natural::TABLE_NAME.'.'.Natural::COLUMN_DICTIONARY['id'].'
            WHERE '.Report::TABLE_NAME.'.'.Report::COLUMN_DICTIONARY['reason'].' IN ('.$in.')
            AND '.Natural::TABLE_NAME.'.'.Natural::COLUMN_DICTIONARY['class'].' = ?;
        ', $sqlArguments, true);
        
        if ($result === false) {
            //Žádná hlášení nenalezena
            return array();
        }
        
        $reports = array();
        foreach ($result as $reportInfo) {
            $natural = new Natural(false, $reportInfo['prirodniny_id']);
            $natural->initialize($reportInfo['prirodniny_nazev'], null, $reportInfo['prirodniny_obrazky']);
            $picture = new Picture(false, $reportInfo['obrazky_id']);
            $picture->initialize($reportInfo['obrazky_zdroj'], $natural, $reportInfo['obrazky_povoleno']);
            $report = new Report(false, $reportInfo['hlaseni_id']);
            $report->initialize($picture, $reportInfo['hlaseni_duvod'], $reportInfo['hlaseni_dalsi_informace'],
                $reportInfo['hlaseni_pocet']);
            $reports[] = $report;
        }
        
        return $reports;
    }

    /**
     * Metoda načítající a navracející pole přírodnin patřících do této třídy jako objekty
     * Data nejsou po navrácení výsledku nikde uložena, proto je v případě opakovaného použití potřeba uložit si je do
     * nějaké proměnné, aby se opakovaným dotazováním databáze nezpomalovalo zpracování požadavku
     * @return Natural[] Pole přírodnin patřících do této třídy jako objekty
     * @throws DatabaseException
     */
    public function getNaturals(): array
    {
        $this->loadIfNotLoaded($this->id);
        $result = Db::fetchQuery('SELECT '.Natural::COLUMN_DICTIONARY['id'].','.Natural::COLUMN_DICTIONARY['name'].','.
            Natural::COLUMN_DICTIONARY['picturesCount'].
            ',(SELECT COUNT(*) FROM prirodniny_casti WHERE prirodniny_id = '.Natural::TABLE_NAME.
            '.'.Natural::COLUMN_DICTIONARY['id'].') AS "uses" FROM '.Natural::TABLE_NAME.' WHERE '.
            Natural::COLUMN_DICTIONARY['class'].' = ?;', array($this->id), true);
        if ($result === false || count($result) === 0) {
            //Žádné přírodniny nenalezeny
            return array();
        } else {
            $naturals = array();
            foreach ($result as $naturalData) {
                $natural = new Natural(false, $naturalData[Natural::COLUMN_DICTIONARY['id']]);
                //Místo posledního null by se mělo nastavit $this, avšak výsledné pole obsahuje příliš mnoho úrovní vnořených objektů a jeho ošetření proti XSS útoku trvá strašně dlouho
                $natural->initialize($naturalData[Natural::COLUMN_DICTIONARY['name']], null,
                    $naturalData[Natural::COLUMN_DICTIONARY['picturesCount']], null, null, $naturalData['uses']);
                $naturals[] = $natural;
            }
        }
        return $naturals;
    }

    /**
     * Metoda navracející pole poznávaček patřících do této třídy jako objekty
     * Pokud zatím nebyly poznávačky načteny, budou načteny z databáze
     * @return Group[] Pole poznávaček patřících do této třídy jako objekty
     * @throws DatabaseException
     */
    public function getGroups(): array
    {
        if (!$this->isDefined($this->groups)) {
            $this->loadGroups();
        }
        return $this->groups;
    }

    /**
     * Metoda načítající poznávačky patřící do této třídy a ukládající je jako vlastnosti do pole jako objekty
     * Počet poznávaček v této třídě je také aktualizován (vlastnost ClassObject::$groupsCount)
     * @throws DatabaseException
     */
    private function loadGroups(): void
    {
        $this->loadIfNotLoaded($this->id);

        $result = Db::fetchQuery('SELECT '.Group::COLUMN_DICTIONARY['id'].','.Group::COLUMN_DICTIONARY['url'].','.
            Group::COLUMN_DICTIONARY['name'].','.Group::COLUMN_DICTIONARY['partsCount'].' FROM '.
            Group::TABLE_NAME.' WHERE '.Group::COLUMN_DICTIONARY['class'].' = ?', array($this->id),
            true);
        if ($result === false || count($result) === 0) {
            //Žádné poznávačky nenalezeny
            $this->groups = array();
        } else {
            $this->groups = array();
            foreach ($result as $groupData) {
                $group = new Group(false, $groupData[Group::COLUMN_DICTIONARY['id']]);
                $group->initialize($groupData[Group::COLUMN_DICTIONARY['name']],
                    $groupData[Group::COLUMN_DICTIONARY['url']], $this, null,
                    $groupData[Group::COLUMN_DICTIONARY['partsCount']]);
                $this->groups[] = $group;
            }
        }

        $this->groupsCount = count($this->groups);
    }

    /**
     * Metoda přidávající do databáze i do instance třídy novou poznávačku
     * @param string $groupName Ošetřený název nové poznávačky
     * @return Group|boolean Objekt vytvořené poznávačky, pokud je poznávačka vytvořena a přidána úspěšně, FALSE, pokud
     *     ne
     * @throws DatabaseException
     */
    public function addGroup(string $groupName)
    {
        if (!$this->isDefined($this->groups)) {
            $this->loadGroups();
        }

        $group = new Group(true);
        $group->initialize($groupName, $this->generateUrl($groupName), $this, null, 0);
        try {
            $result = $group->save();
            if ($result) {
                $this->groups[] = $group;
                return $group;
            }
        } catch (BadMethodCallException $e) {
        }

        return false;
    }

    /**
     *
     * Metoda odstraňující danou poznávačku z této třídy i z databáze
     * @param Group $group Objekt poznávačky, který má být odstraněn
     * @return boolean TRUE, v případě, že se odstranění poznávačky povede, FALSE, pokud ne
     * @throws DatabaseException
     * @throws BadMethodCallException Pokud daná poznávačka není součástí této třídy
     */
    public function removeGroup(Group $group): bool
    {
        $groupId = $group->getId();

        if (!$this->isDefined($this->groups)) {
            $this->loadGroups();
        }

        //Získání indexu, pod kterým je uložena odstraňovaná poznávačka
        for ($i = 0; $i < count($this->groups) && $this->groups[$i]->getId() != $groupId; $i++) {
        }

        if ($i === count($this->groups)) {
            (new Logger())->warning('Uživatel s ID {userId} se pokusil ze třídy s ID {classId} odstranit poznávačku s ID {groupId} z IP adresy {ip}, avšak daná poznávačka nebyla v dané třídě nalezena',
                array(
                    'userId' => UserManager::getId(),
                    'classId' => $this->getId(),
                    'groupId' => $groupId,
                    'ip' => $_SERVER['REMOTE_ADDR']
                ));
            throw new BadMethodCallException("Tato poznávačka není součástí této třídy a tudíž z ní nemůže být odstraněna");
        }

        //Odebrání odkazu na poznávačku z této instance třídy
        array_splice($this->groups, $i, 1);

        //Odstranění poznávačky z databáze
        $result = $group->delete();
        (new Logger())->info('Uživatel s ID {userId} odstranil ze třídy s ID {classId} poznávačku s ID {groupId} z IP adresy {ip}',
            array(
                'userId' => UserManager::getId(),
                'classId' => $this->getId(),
                'groupId' => $groupId,
                'ip' => $_SERVER['REMOTE_ADDR']
            ));
        return $result;
    }

    /**
     * Metoda navracející pole členů této třídy jako objekty
     * Pokud zatím nebyly členové načteny, budou načteny z databáze
     * @param boolean $includeLogged TRUE, pokud má být navrácen i záznam přihlášeného uživatele
     * @return User[] Pole členů patřících do této třídy jako instance třídy User
     * @throws AccessDeniedException Pokud není přihlášen žádný uživatel
     * @throws DatabaseException
     */
    public function getMembers($includeLogged = true): array
    {
        if (!$this->isDefined($this->members)) {
            $this->loadMembers();
        }
        $result = $this->members;
        if (!$includeLogged && count($result) > 0) {
            //Odeber z kopie pole členů přihlášeného uživatele
            for ($i = 0; $i < count($result) && $result[$i]->getId() !== UserManager::getId(); $i++) {
            }
            if ($i < count($result)) {
                array_splice($result, $i, 1);
            } //Pro případ, že by přihlášený uživatel nebyl členem třídy - pokud je právě třída spravována systémovým administrátorem
        }
        return $result;
    }

    /**
     * Metoda načítající členy patřící do této třídy a ukládající je jako vlastnosti do pole jako objekty
     * @throws DatabaseException
     * @throws Exception Pokud se nepodaří vytvořit objekt DateTime
     */
    public function loadMembers(): void
    {
        $this->loadIfNotLoaded($this->id);

        $result = Db::fetchQuery('SELECT '.User::TABLE_NAME.'.'.User::COLUMN_DICTIONARY['id'].','.User::TABLE_NAME.'.'.
            User::COLUMN_DICTIONARY['name'].','.User::TABLE_NAME.'.'.
            User::COLUMN_DICTIONARY['email'].','.User::TABLE_NAME.'.'.
            User::COLUMN_DICTIONARY['lastLogin'].','.User::TABLE_NAME.'.'.
            User::COLUMN_DICTIONARY['addedPictures'].','.User::TABLE_NAME.'.'.
            User::COLUMN_DICTIONARY['guessedPictures'].','.User::TABLE_NAME.'.'.
            User::COLUMN_DICTIONARY['karma'].','.User::TABLE_NAME.'.'.
            User::COLUMN_DICTIONARY['status'].' FROM clenstvi JOIN '.User::TABLE_NAME.
            ' ON clenstvi.uzivatele_id = '.User::TABLE_NAME.'.'.User::COLUMN_DICTIONARY['id'].
            ' WHERE clenstvi.tridy_id = ? ORDER BY '.User::TABLE_NAME.'.'.
            User::COLUMN_DICTIONARY['lastLogin'].' DESC;', array($this->id), true);
        if ($result === false || count($result) === 0) {
            //Žádní členové nenalezeni
            $this->members = array();
        } else {
            $this->members = array();
            foreach ($result as $memberData) {
                $user = new User(false, $memberData[User::COLUMN_DICTIONARY['id']]);
                $user->initialize($memberData[User::COLUMN_DICTIONARY['name']],
                    $memberData[User::COLUMN_DICTIONARY['email']],
                    new DateTime($memberData[User::COLUMN_DICTIONARY['lastLogin']]),
                    $memberData[User::COLUMN_DICTIONARY['addedPictures']],
                    $memberData[User::COLUMN_DICTIONARY['guessedPictures']],
                    $memberData[User::COLUMN_DICTIONARY['karma']], $memberData[User::COLUMN_DICTIONARY['status']]);
                $this->members[] = $user;
            }
        }
    }

    /**
     * Metoda vytvářející pozvánku do této třídy pro určitého uživatele
     * Pokud byl již uživatel do této třídy pozván, je prodloužena životnost existující pozvánky
     * Pokud pozvaný uživatel představuje demo účet, není jeho pozvání do třídy možné
     * @param string $userName Jméno uživatele, pro kterého je pozvánka určena
     * @return boolean TRUE, pokud je pozvánka úspěšně vytvořena
     * @throws DatabaseException
     * @throws AccessDeniedException Pokud je tato třída veřejná, uživatel se zadaným jménem představuje demo účet,
     *     neexistuje nebo je již členem této třídy
     * @throws Exception Pokud se nepodaří vytvořit objekt DateTime
     */
    public function inviteUser(string $userName): bool
    {
        $this->loadIfNotLoaded($this->id);

        //Konstrukce objektu uživatele
        $result = Db::fetchQuery('SELECT '.User::COLUMN_DICTIONARY['id'].','.User::COLUMN_DICTIONARY['name'].','.
            User::COLUMN_DICTIONARY['email'].','.User::COLUMN_DICTIONARY['lastLogin'].','.
            User::COLUMN_DICTIONARY['addedPictures'].','.
            User::COLUMN_DICTIONARY['guessedPictures'].','.User::COLUMN_DICTIONARY['karma'].','.
            User::COLUMN_DICTIONARY['status'].' FROM '.User::TABLE_NAME.' WHERE '.
            User::COLUMN_DICTIONARY['name'].' = ? LIMIT 1', array($userName));
        if (empty($result)) {
            (new Logger())->notice('Uživatel s ID {userId} se pokusil z IP adresy {ip} odeslat pozvánku do třídy s ID {classId} pro uživatele se jménem {invitedUserName}, avšak uživatel s tímto jménem nebyl nalezen',
                array(
                    'userId' => UserManager::getId(),
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    'classId' => $this->getId(),
                    'invitedUserName' => $userName
                ));
            throw new AccessDeniedException(AccessDeniedException::REASON_MANAGEMENT_INVITE_USER_UNKNOWN_USER);
        }
        $user = new User(false, $result[User::COLUMN_DICTIONARY['id']]);
        $user->initialize($result[User::COLUMN_DICTIONARY['name']], $result[User::COLUMN_DICTIONARY['email']],
            new DateTime($result[User::COLUMN_DICTIONARY['lastLogin']]),
            $result[User::COLUMN_DICTIONARY['addedPictures']], $result[User::COLUMN_DICTIONARY['guessedPictures']],
            $result[User::COLUMN_DICTIONARY['karma']], $result[User::COLUMN_DICTIONARY['status']]);

        //Zkontroluj, zda uživatel již není členem třídy
        for ($i = 0; $i < count($this->members) && $user->getId() !== $this->members[$i]->getId(); $i++) {
        }
        if ($i !== count($this->members)) {
            (new Logger())->notice('Uživatel s ID {userId} se pokusil z IP adresy {ip} odeslat pozvánku do třídy s ID {classId} pro uživatele s ID {invitedUserId}, avšak daný uživatel již je členem dané třídy',
                array(
                    'userId' => UserManager::getId(),
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    'classId' => $this->getId(),
                    'invitedUserId' => $user->getId()
                ));
            throw new AccessDeniedException(AccessDeniedException::REASON_MANAGEMENT_INVITE_USER_ALREADY_MEMBER);
        }

        //Zkontroluj, zda uživatel nepředstavuje demo účet
        if ($user['status'] === User::STATUS_GUEST) {
            (new Logger())->notice('Uživatel s ID {userId} se pokusil z IP adresy {ip} odeslat pozvánku do třídy s ID {classId} pro uživatele s ID {invitedUserId}, avšak daný uživatel je demo účet',
                array(
                    'userId' => UserManager::getId(),
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    'classId' => $this->getId(),
                    'invitedUserId' => $user->getId()
                ));
            throw new AccessDeniedException(AccessDeniedException::REASON_MANAGEMENT_INVITE_USER_DEMO_ACCOUNT);
        }

        //Ověř, zda již taková pozvánka v databázi neexistuje
        $result = Db::fetchQuery('SELECT '.Invitation::COLUMN_DICTIONARY['id'].' FROM '.Invitation::TABLE_NAME.
            ' WHERE '.Invitation::COLUMN_DICTIONARY['user'].' = ? AND '.
            Invitation::COLUMN_DICTIONARY['class'].' = ? LIMIT 1',
            array($user->getId(), $this->id));
        if (empty($result)) {
            //Nová pozvánka
            (new Logger())->info('Uživatel s ID {userId} odeslal z IP adresy {ip} pozvánku do třídy s ID {classId} pro uživatele s ID {invitedUserId}',
                array(
                    'userId' => UserManager::getId(),
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    'classId' => $this->getId(),
                    'invitedUserId' => $user->getId()
                ));
            $invitation = new Invitation(true);
        } else {
            //Prodloužit životnost existující pozvánky
            (new Logger())->info('Uživatel s ID {userId} odeslal z IP adresy {ip} pozvánku do třídy s ID {classId} pro uživatele s ID {invitedUserId}, čímž prodloužil platnost již existující pozvánky',
                array(
                    'userId' => UserManager::getId(),
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    'classId' => $this->getId(),
                    'invitedUserId' => $user->getId()
                ));
            $invitation = new Invitation(false, $result[Invitation::COLUMN_DICTIONARY['id']]);
        }

        $expiration = new DateTime('@'.(time() + Settings::INVITATION_LIFETIME));
        $invitation->initialize($user, $this, $expiration);
        $invitation->save();

        return true;
    }

    /**
     * Metoda přidávající uživatele do třídy (přidává spojení uživatele a třídy do tabulky "clenstvi")
     * Pokud je tato třída veřejná nebo uzamčená, nic se nestane
     * @param int $userId ID uživatele získávajícího členství
     * @return boolean TRUE, pokud je členství ve třídě úspěšně přidáno, FALSE, pokud ne
     * @throws DatabaseException Pokud se při práci s databází vyskytne chyba
     */
    public function addMember(int $userId): bool
    {
        $this->loadIfNotLoaded($this->id);

        //Zkontroluj, zda je třída soukromá
        //Není třeba - před zavoláním této metody při získávání členství pomocí kódu je zkontrolováno, zda není třída zamknutá
        # if (!$this->getStatus() === self::CLASS_STATUS_PRIVATE)
        # {
        #     //Nelze získat členství ve veřejné nebo uzamčené třídě
        #     return false;
        # }

        //Zkontroluj, zda již uživatel není členem této třídy
        //Není třeba - metoda getNewClassesByAccessCode ve třídě ClassManager navrací pouze třídy, ve kterých přihlášený uživatel ještě není členem
        # if ($this->checkAccess($userId))
        # {
        #     //Nelze získat členství ve třídě vícekrát
        #     return false;
        # }

        Db::executeQuery('INSERT INTO clenstvi (uzivatele_id,tridy_id) VALUES (?,?)', array($userId, $this->id));

        return true;
    }

    /**
     * Metoda odstraňující uživatele ze třídy (odstraňuje spojení uživatele a třídy z tabulky "clenstvi")
     * Pokud je třída veřejná, nic se nestane
     * @param int $userId
     * @return boolean TRUE, v případě, že se odstranění uživatele povede
     * @throws DatabaseException
     * @throws AccessDeniedException Pokud se jedná o veřejnou třídu, pokud uživatel s daným ID není členem této třídy
     *     nebo pokud se vyskytne chyba při odstraňování iživatelova členství z databáze
     */
    public function removeMember(int $userId): bool
    {
        $this->loadIfNotLoaded($this->status);

        $this->loadIfNotLoaded($this->admin);

        if ($userId === $this->admin->getId()) {
            //Správce třídy nemůže sám sebe vyhodit
            (new Logger())->warning('Uživatel s ID {userId} se pokusil ze třídy s ID {classId} odebrat sám sebe z IP adresy {ip}, což správce udělat nemůže',
                array('userId' => UserManager::getId(), 'classId' => $this->getId(), 'ip' => $_SERVER['REMOTE_ADDR']));
            throw new AccessDeniedException(AccessDeniedException::REASON_MANAGEMENT_KICK_USER_CANT_SELF);
        }

        $this->loadIfNotLoaded($this->id);
        if (!$this->isDefined($this->members)) {
            $this->loadMembers();
        }

        //Odebrat uživatele z pole uživatelů v objektu třídy
        for ($i = 0; $i < count($this->members); $i++) {
            if ($this->members[$i]->getId() === $userId) {
                array_splice($this->members, $i, 1);
                $i = count($this->members) + 1;
            }
        }
        if ($i === count($this->members)) {
            (new Logger())->warning('Uživatel s ID {userId} se pokusil ze třídy s ID {classId} odebrat uživatele s ID {kickedUserId} z IP adresy {ip}, avšak daný uživatel není členem dané třídy',
                array(
                    'userId' => UserManager::getId(),
                    'classId' => $this->getId(),
                    'kickedUserId' => $userId,
                    'ip' => $_SERVER['REMOTE_ADDR']
                ));
            throw new AccessDeniedException(AccessDeniedException::REASON_MANAGEMENT_KICK_USER_NOT_A_MEMBER);
        }

        //Odstranit členství z databáze
        if (Db::executeQuery('DELETE FROM clenstvi WHERE tridy_id = ? AND uzivatele_id = ? LIMIT 1',
            array($this->id, $userId))) {
            (new Logger())->info('Uživatel s ID {userId} odebral ze třídy s ID {classId} uživatele s ID {kickedUserId} z IP adresy {ip}',
                array(
                    'userId' => UserManager::getId(),
                    'classId' => $this->getId(),
                    'kickedUserId' => $userId,
                    'ip' => $_SERVER['REMOTE_ADDR']
                ));
            return true;
        } else {
            (new Logger())->error('Uživatel s ID {userId} se pokusil ze třídy s ID {classId} odebrat uživatele s ID {kickedUserId} z IP adresy {ip}, avšak zabránila mu v tom nečekaná chyba databáze',
                array(
                    'userId' => UserManager::getId(),
                    'classId' => $this->getId(),
                    'kickedUserId' => $userId,
                    'ip' => $_SERVER['REMOTE_ADDR']
                ));
            throw new AccessDeniedException(AccessDeniedException::REASON_UNEXPECTED);
        }
    }

    /**
     * Metoda zjišťující, zda jsou poznávačky patřící do této třídy načteny (i když třeba žádné neexistují), nebo ne
     * @return bool TRUE, pokud jsou poznávačky této třídy načteny, FALSE, pokud je vlastnost $groups nastavena na
     *     undefined
     */
    public function areGroupsLoaded(): bool
    {
        return $this->isDefined($this->groups);
    }

    /**
     * Metoda kontrolující, zda má určitý uživatel přístup do této třídy
     * Pokud přihlášený uživatel využívá demo účet, je přístup povolen pouze v případě, že je tato třída uzamčená
     * @param int $userId ID ověřovaného uživatele
     * @param bool $forceAgain Pokud je tato funkce jednou zavolána, uloží se její výsledek jako vlastnost tohoto
     *     objektu třídy a příště se použije namísto dalšího databázového dotazu. Pokud tuto hodnotu nastavíte na TRUE,
     *     bude znovu poslán dotaz na databázi. Defaultně FALSE
     * @return boolean TRUE, pokud má uživatel přístup do třídy, FALSE pokud ne
     * @throws AccessDeniedException Pokud není přihlášen žádný uživatel
     * @throws DatabaseException
     */
    public function checkAccess(int $userId, bool $forceAgain = false): bool
    {
        if (isset($this->accessCheckResult) && $forceAgain === false) {
            return $this->accessCheckResult;
        }

        $this->loadIfNotLoaded($this->id);

        $checker = new AccessChecker();
        if ($checker->checkDemoAccount()) {
            //Pokud není tato třída uzamčená, neposkytni přístup
            $result = Db::fetchQuery('SELECT COUNT(*) AS "cnt" FROM '.self::TABLE_NAME.' WHERE '.
                self::COLUMN_DICTIONARY['id'].' = ? AND ('.self::COLUMN_DICTIONARY['status'].
                ' = "locked" AND '.self::COLUMN_DICTIONARY['id'].
                ' IN (SELECT tridy_id FROM clenstvi WHERE uzivatele_id = ?));',
                array($this->id, $userId), false);
        } else {
            $result = Db::fetchQuery('SELECT COUNT(*) AS "cnt" FROM '.self::TABLE_NAME.' WHERE '.
                self::COLUMN_DICTIONARY['id'].' = ? AND ('.self::COLUMN_DICTIONARY['status'].
                ' = "public" OR '.self::COLUMN_DICTIONARY['id'].
                ' IN (SELECT tridy_id FROM clenstvi WHERE uzivatele_id = ?));',
                array($this->id, $userId), false);
        }

        $this->accessCheckResult = $result['cnt'] == 1;
        return $result['cnt'] == 1;
    }

    /**
     * Metoda kontrolující, zda je uživatel se zadaným ID členem v této třídě (tedy zda do ní získal přístup na základě
     * pozvánky, nebo zadáním vstupního kódu).
     * @param int $userId ID uživatele ke kontrole
     * @return bool TRUE, pokud je uživatel členem této třídy, FALSE, pokud ne
     */
    public function isMember(int $userId): bool
    {
        $result = Db::fetchQuery('SELECT COUNT(*) AS "cnt" FROM clenstvi WHERE tridy_id = ? AND uzivatele_id = ?;',
            array($this->id, $userId), false);
        return ($result['cnt'] === 1);
    }

    /**
     * Metoda kontrolující, zda je určitý uživatel správcem této třídy
     * Pokud zatím nebyl načten správce této třídy, bude načten z databáze
     * @param int $userId ID ověřovaného uživatele
     * @return boolean TRUE, pokud je uživatelem správce třídy, FALSE pokud ne
     * @throws DatabaseException
     */
    public function checkAdmin(int $userId): bool
    {
        $this->loadIfNotLoaded($this->admin);
        return $this->admin->getId() === $userId;
    }

    /**
     * Metoda ukládající do databáze nový požadavek na změnu názvu této třídy vyvolaný správcem této třídy, pokud žádný
     * takový požadavek neexistuje nebo aktualizující stávající požadavek Data jsou předem ověřena
     * @param string $newName Požadovaný nový název
     * @return boolean TRUE, pokud je žádost úspěšně vytvořena/aktualizována
     * @throws DatabaseException
     * @throws AccessDeniedException Pokud jméno nevyhovuje podmínkám systému
     * @throws Exception Pokud se nepodaří vytvořit objekt DateTime
     */
    public function requestNameChange(string $newName): bool
    {
        if (mb_strlen($newName) === 0) {
            (new Logger())->notice('Uživatel s ID {userId} se pokusil zažádat o změnu názvu třídy s ID {classId} z IP adresy {ip}, avšak nevyplnil požadovaný název',
                array('userId' => UserManager::getId(), 'classId' => $this->getId(), 'ip' => $_SERVER['REMOTE_ADDR']));
            throw new AccessDeniedException(AccessDeniedException::REASON_MANAGEMENT_NAME_CHANGE_NO_NAME);
        }

        //Kontrola délky názvu
        $validator = new DataValidator();
        try {
            $validator->checkLength($newName, DataValidator::CLASS_NAME_MIN_LENGTH,
                DataValidator::CLASS_NAME_MAX_LENGTH, DataValidator::TYPE_CLASS_NAME);
        } catch (RangeException $e) {
            (new Logger())->notice('Uživatel s ID {userId} se pokusil zažádat o změnu názvu třídy s ID {classId} na {newName} z IP adresy {ip}, avšak neuspěl kvůli nepřijatelné délce požadovaného názvu',
                array(
                    'userId' => UserManager::getId(),
                    'classId' => $this->getId(),
                    'newName' => $newName,
                    'ip' => $_SERVER['REMOTE_ADDR']
                ));
            if ($e->getMessage() === 'long') {
                throw new AccessDeniedException(AccessDeniedException::REASON_MANAGEMENT_NAME_CHANGE_NAME_TOO_LONG,
                    null, $e);
            } else {
                if ($e->getMessage() === 'short') {
                    throw new AccessDeniedException(AccessDeniedException::REASON_MANAGEMENT_NAME_CHANGE_NAME_TOO_SHORT,
                        null, $e);
                }
            }
        }

        //Kontrola znaků v názvu
        try {
            $validator->checkCharacters($newName, DataValidator::CLASS_NAME_ALLOWED_CHARS,
                DataValidator::TYPE_CLASS_NAME);
        } catch (InvalidArgumentException $e) {

            (new Logger())->notice('Uživatel s ID {userId} se pokusil zažádat o změnu názvu třídy s ID {classId} na {newName} z IP adresy {ip}, avšak neuspěl kvůli přítomnosti nepovolených znaků v požadovaném názvu',
                array(
                    'userId' => UserManager::getId(),
                    'classId' => $this->getId(),
                    'newName' => $newName,
                    'ip' => $_SERVER['REMOTE_ADDR']
                ));
            throw new AccessDeniedException(AccessDeniedException::REASON_MANAGEMENT_NAME_CHANGE_INVALID_CHARACTERS,
                null, $e);
        }

        //Kontrola dostupnosti jména (konkrétně URL adresy)
        $url = $this->generateUrl($newName);
        try {
            $validator->checkUniqueness($url, DataValidator::TYPE_CLASS_URL);
        } catch (InvalidArgumentException $e) {

            (new Logger())->notice('Uživatel s ID {userId} se pokusil zažádat o změnu názvu třídy s ID {classId} na {newName} z IP adresy {ip}, avšak třída se stejnou URL reprezentací názvu již existuje',
                array(
                    'userId' => UserManager::getId(),
                    'classId' => $this->getId(),
                    'newName' => $newName,
                    'ip' => $_SERVER['REMOTE_ADDR']
                ));
            throw new AccessDeniedException(AccessDeniedException::REASON_MANAGEMENT_NAME_CHANGE_DUPLICATE_NAME, null,
                $e);
        }

        //Kontrola, zda URL třídy není rezervované pro žádný kontroler
        try {
            $validator->checkForbiddenUrls($url, DataValidator::TYPE_CLASS_URL);
        } catch (InvalidArgumentException $e) {
            (new Logger())->notice('Uživatel s ID {userId} se pokusil zažádat o změnu názvu třídy s ID {classId} na {newName} z IP adresy {ip}, avšak URL reprezentace požadovaného názvu je rezervována',
                array(
                    'userId' => UserManager::getId(),
                    'classId' => $this->getId(),
                    'newName' => $newName,
                    'ip' => $_SERVER['REMOTE_ADDR']
                ));
            throw new AccessDeniedException(AccessDeniedException::REASON_MANAGEMENT_NAME_CHANGE_FORBIDDEN_URL, null,
                $e);
        }

        //Kontrola dat OK

        $this->loadIfNotLoaded($this->id);

        //Zkontrolovat, zda již existuje žádost o změnu názvu této třídy
        $applications = Db::fetchQuery('SELECT '.ClassNameChangeRequest::COLUMN_DICTIONARY['id'].' FROM '.
            ClassNameChangeRequest::TABLE_NAME.' WHERE '.
            ClassNameChangeRequest::COLUMN_DICTIONARY['subject'].' = ? LIMIT 1',
            array($this->id));
        if (!empty($applications[ClassNameChangeRequest::COLUMN_DICTIONARY['id']])) {
            //Přepsání existující žádosti
            $request = new ClassNameChangeRequest(false,
                $applications[ClassNameChangeRequest::COLUMN_DICTIONARY['id']]);
            $request->initialize($this, $newName, new DateTime('@'.time()), $this->generateUrl($newName));
            $request->save();
            (new Logger())->info('Uživatel s ID {userId} se zažádal o změnu názvu třídy s ID {classId} na {newName} z IP adresy {ip}, tímto přepsal již existující žádost o změnu',
                array(
                    'userId' => UserManager::getId(),
                    'classId' => $this->getId(),
                    'newName' => $newName,
                    'ip' => $_SERVER['REMOTE_ADDR']
                ));
        } else {
            //Uložení nové žádosti
            $request = new ClassNameChangeRequest(true);
            $request->initialize($this, $newName, new DateTime('@'.time()), $this->generateUrl($newName));
            $request->save();
            (new Logger())->info('Uživatel s ID {userId} se zažádal o změnu názvu třídy s ID {classId} na {newName} z IP adresy {ip}',
                array(
                    'userId' => UserManager::getId(),
                    'classId' => $this->getId(),
                    'newName' => $newName,
                    'ip' => $_SERVER['REMOTE_ADDR']
                ));
        }
        return true;
    }

    /**
     * Metoda upravující přístupová data této třídy z rozhodnutí administrátora
     * @param string $status Nový status třídy (musí být jedna z konstant této třídy)
     * @param int|null $code Nový přístupový kód třídy (nepovinné, pokud je status nastaven na "public" nebo "locked")
     * @return boolean TRUE, pokud jsou přístupová data třídy úspěšně aktualizována
     * @throws DatabaseException
     * @throws AccessDeniedException Pokud není přihlášený uživatel administrátorem nebo jsou zadaná data neplatná
     */
    public function updateAccessDataAsAdmin(string $status, ?int $code): bool
    {
        //Nastavení kódu na NULL, pokud je třída nastavená na status, ve kterém by neměl smysl
        if ($status === self::CLASS_STATUS_PUBLIC || $status === self::CLASS_STATUS_LOCKED) {
            $code = null;
        }

        //Kontrola, zda je právě přihlášený uživatelem administrátorem
        $aChecker = new AccessChecker();
        if (!$aChecker->checkSystemAdmin()) {
            throw new AccessDeniedException(AccessDeniedException::REASON_INSUFFICIENT_PERMISSION);
        }

        $validator = new DataValidator();
        //Kontrola platnosti dat
        if (($code !== null && !($validator->validateClassCode($code))) ||
            !($status === self::CLASS_STATUS_PUBLIC || $status === self::CLASS_STATUS_PRIVATE ||
                $status === self::CLASS_STATUS_LOCKED)) {
            throw new AccessDeniedException(AccessDeniedException::REASON_ADMINISTRATION_CLASS_UPDATE_INVALID_DATA);
        }

        //Kontrola dat OK

        $this->loadIfNotLoaded($this->id);

        $this->status = $status;
        $this->code = $code;

        Db::executeQuery('UPDATE '.self::TABLE_NAME.' SET '.self::COLUMN_DICTIONARY['status'].' = ?, '.
            self::COLUMN_DICTIONARY['code'].' = ? WHERE '.self::COLUMN_DICTIONARY['id'].' = ? LIMIT 1;',
            array($status, $code, $this->id), false);

        return true;
    }

    /**
     * Metoda upravující přístupová data této třídy po jejich změně správcem třídy
     * @param string $status Nový status třídy (musí být jedna z konstant této třídy)
     * @param int|null $code Nový přístupový kód třídy (nepovinné, pokud je status nastaven na "public" nebo "locked"),
     *     v případě že není typu int nebo NULL, bude vyhozena výjimka signalizující uživatelskou chybu - neplatný
     *     formát vstupního kódu
     * @param bool $readonly Nové nastavené "jen pro čtení" (true, pokud pouze členové třídy, kteří dříve zadali
     *     vstupní kód třídy, nebo byli pozváni, mohou přidávat obrázky)
     * @return bool TRUE, pokud jsou přístupová data třídy úspěšně aktualizována
     * @throws AccessDeniedException Pokud jsou zadaná data neplatná
     * @throws DatabaseException
     */
    public function updateAccessData(string $status, ?int $code, bool $readonly): bool
    {
        //Nastavení kódu na NULL, pokud je třída nastavená na status, ve kterém by neměl smysl
        if ($status === self::CLASS_STATUS_PUBLIC || $status === self::CLASS_STATUS_LOCKED) {
            $code = null;
        }

        //Kontrola platnosti dat
        $validator = new DataValidator();
        if (!($status === self::CLASS_STATUS_PUBLIC || $status === self::CLASS_STATUS_PRIVATE ||
            $status === self::CLASS_STATUS_LOCKED)) {

            (new Logger())->warning('Uživatel s ID {userId} se pokusil změnit stav třídy s ID {classId} z IP adresy {ip}, avšak ten nebyl rozpoznán',
                array(
                    'userId' => UserManager::getId(),
                    'classId' => $this->getId(),
                    'newStatus' => $status,
                    'ip' => $_SERVER['REMOTE_ADDR']
                ));
            throw new AccessDeniedException(AccessDeniedException::REASON_MANAGEMENT_ACCESS_CHANGE_INVALID_STATUS);
        }
        if ($code !== null && !($validator->validateClassCode($code))) {
            (new Logger())->warning('Uživatel s ID {userId} se pokusil změnit přístupový kód třídy s ID {classId} na {newCode} z IP adresy {ip}, avšak kód neměl správný formát',
                array(
                    'userId' => UserManager::getId(),
                    'classId' => $this->getId(),
                    'newCode' => $code,
                    'ip' => $_SERVER['REMOTE_ADDR']
                ));
            throw new AccessDeniedException(AccessDeniedException::REASON_MANAGEMENT_ACCESS_CHANGE_INVALID_CODE);
        }

        //Kontrola dat OK

        $this->loadIfNotLoaded($this->id);

        $this->status = $status;
        $this->code = $code;
        $this->readonly = $readonly;

        Db::executeQuery('UPDATE '.self::TABLE_NAME.' SET '.self::COLUMN_DICTIONARY['status'].' = ?, '.
            self::COLUMN_DICTIONARY['code'].' = ?, '.self::COLUMN_DICTIONARY['readonly'].' = ? '.
            'WHERE '.self::COLUMN_DICTIONARY['id'].' = ? LIMIT 1;',
            array($status, $code, $readonly ? 1 : 0, $this->id), false);
        (new Logger())->info('Uživatel s ID {userId} změnil stav třídy s ID {classId} na {newStatus}, její kód nastavil na {newCode} a třídu nastavil jako {readonly} pouze pro čtení z IP adresy {ip}',
            array(
                'userId' => UserManager::getId(),
                'classId' => $this->getId(),
                'newStatus' => $status,
                'newCode' => $code,
                'readonly' => ($readonly) ? '' : 'ne',
                'ip' => $_SERVER['REMOTE_ADDR']
            ));

        return true;
    }

    /**
     * Metoda měnící správce této třídy z rozhodnutí administrátora
     * @param User $newAdmin Instance třídy uživatele reprezentující nového správce
     * @return boolean TRUE, pokud jsou přístupová data třídy úspěšně aktualizována
     * @throws DatabaseException
     * @throws AccessDeniedException Pokud není přihlášený uživatel administrátorem
     */
    public function changeClassAdminAsAdmin(User $newAdmin): bool
    {
        //Kontrola, zda je právě přihlášený uživatelem administrátorem
        $aChecker = new AccessChecker();
        if (!$aChecker->checkSystemAdmin()) {
            throw new AccessDeniedException(AccessDeniedException::REASON_INSUFFICIENT_PERMISSION);
        }

        //Kontrola dat OK (zda uživatel s tímto ID exisutje je již zkontrolováno v Administration::changeClassAdmin())

        $this->admin = $newAdmin;

        $this->loadIfNotLoaded($this->id);

        Db::executeQuery('UPDATE '.self::TABLE_NAME.' SET '.self::COLUMN_DICTIONARY['admin'].' = ? WHERE '.
            self::COLUMN_DICTIONARY['id'].' = ? LIMIT 1;', array($newAdmin->getId(), $this->id));

        return true;
    }

    /**
     * Metoda odstraňující tuto třídu z databáze na základě rozhodnutí administrátora
     * @return boolean TRUE, pokud je třída úspěšně odstraněna z databáze
     * @throws DatabaseException
     * @throws AccessDeniedException Pokud není přihlášený uživatel administrátorem
     */
    public function deleteAsAdmin(): bool
    {
        //Kontrola, zda je právě přihlášený uživatelem administrátorem
        $aChecker = new AccessChecker();
        if (!$aChecker->checkSystemAdmin()) {
            throw new AccessDeniedException(AccessDeniedException::REASON_INSUFFICIENT_PERMISSION);
        }

        //Kontrola dat OK

        $this->delete();

        return true;
    }

    /**
     * Metoda odstraňující tuto třídu z databáze na základě rozhodnutí jejího správce
     * @param string $password Heslo správce třídy pro ověření
     * @return boolean TRUE, pokud je třída úspěšně odstraněna z databáze
     * @throws DatabaseException
     * @throws AccessDeniedException Pokud přihlášený uživatel není správcem této třídy nebo zadal špatné / žádné heslo
     */
    public function deleteAsClassAdmin(string $password): bool
    {
        $classId = $this->getId();

        //Kontrola, zda je přihlášený uživatel správcem této třídy
        if (!$this->checkAdmin(UserManager::getId())) {
            (new Logger())->warning('Uživatel s ID {userId} se pokusil z IP adresy {ip} odstranit třídu s ID {classId}, avšak selhal, protože není jejím správcem',
                array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR'], 'classId' => $classId));
            throw new AccessDeniedException(AccessDeniedException::REASON_INSUFFICIENT_PERMISSION);
        }

        //Kontrola hesla
        if (mb_strlen($password) === 0) {
            (new Logger())->warning('Uživatel s ID {userId} se pokusil z IP adresy {ip} odstranit třídu s ID {classId}, avšak selhal, protože dodatečná kontrola jeho hesla zjistila jeho nevyplnění',
                array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR'], 'classId' => $classId));
            throw new AccessDeniedException(AccessDeniedException::REASON_NO_PASSWORD_GENERAL);
        }
        $aChecker = new AccessChecker();
        if (!$aChecker->recheckPassword($password)) {
            (new Logger())->warning('Uživatel s ID {userId} se pokusil z IP adresy {ip} odstranit třídu s ID {classId}, avšak selhal, protože dodatečná kontrola jeho hesla neprošla',
                array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR'], 'classId' => $classId));
            throw new AccessDeniedException(AccessDeniedException::REASON_WRONG_PASSWORD_GENERAL);
        }

        //Kontrola dat OK

        //Odstranit třídu z databáze
        $result = $this->delete();

        //Zrušit výběr této třídy (a jejích podčástí) v $_SESSION['selection']
        unset($_SESSION['selection']);

        //Zkontrolovat, zda (nyní již bývalý) správce třídy spravuje ještě nějakou třídu a případně mu odebrat status Class Owner
        $result = Db::fetchQuery('SELECT COUNT(*) AS "cnt"
            FROM '.ClassObject::TABLE_NAME.' 
            WHERE '.ClassObject::COLUMN_DICTIONARY['admin'].' = ?',
            array(UserManager::getId()));
        $result = $result['cnt'];
        if ($result === 0 || UserManager::getOtherInformation()['status'] === User::STATUS_CLASS_OWNER) {
            //Změň správcův status (pokud není správcem systému)
            Db::executeQuery('UPDATE '.User::TABLE_NAME.' 
                SET '.User::COLUMN_DICTIONARY['status'].' = ? 
                WHERE '.User::COLUMN_DICTIONARY['id'].' = ?;',
                array(User::STATUS_MEMBER, UserManager::getId()));
            //Změň status uživatele i v sezení
            $user = UserManager::getUser();
            $user->initialize(null, null, null, null, null, null, User::STATUS_MEMBER);
        }

        (new Logger())->info('Uživatel s ID {userId} odstranil z IP adresy {ip} třídu s ID {classId}',
            array('userId' => UserManager::getId(), 'ip' => $_SERVER['REMOTE_ADDR'], 'classId' => $classId));
        return $result;
    }
}

