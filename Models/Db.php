<?php
/** 
 * PDO databázový wrapper
 * @author Jan Štěch
 */
class Db
{
    private const DEFAULT_HOST = 'localhost';
    private const DEFAULT_USERNAME = 'root';
    private const DEFAULT_PASSWORD = '';
    private const DEFAULT_DATABASE = 'poznavacky';
    
    private static $connection;
    private static $settings = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
        PDO::ATTR_EMULATE_PREPARES => false
    );
    
    /**
     * Metoda zakládající spojení s databází
     * 
     * Nové spojení je vytvořeno pouze v případě, že již nějaké neexistuje
     * @param string $host  Server hostující databázi
     * @param string $username  Přihlašovací jméno pro databázi
     * @param string $password  Heslo k databázi
     * @param string $database  Jméno databáze
     * @return PDO Připojení k databázi
     */
    public static function connect(string $host = self::DEFAULT_HOST, string $username = self::DEFAULT_USERNAME, string $password = self::DEFAULT_PASSWORD, string $database = self::DEFAULT_DATABASE)
    {
        if (!isset(self::$connection))
        {
            self::$connection = new PDO('mysql:host='.$host.';dbname='.$database, $username, $password, self::$settings);
        }
        return self::$connection;
    }
    
    /**
     * Metoda ničící PDO objekt zajišťující spojení s databází
     */
    public static function disconnect()
    {
        unset(self::$connection);
    }
    
    /**
     * Metoda provádějící SQL dotaz na databázi bez navracení výsledků
     * 
     * Vhodné pro dotazy jako INSERT, UPDATE a DELETE
     * @param string $query Dotaz pro provedení s otazníky místo parametrů
     * @param array $parameters Pole parametrů, které budou doplněny místo otazníků do dotazu¨
     * @return bool TRUE, pokud dotaz neselhal
     * @throws DatabaseException V případě selhání dotazu
     */
    public static function executeQuery(string $query, array $parameters = array())
    {
        try
        {
            $statement = self::$connection->prepare($query);
            $result = $statement->execute($parameters);
        }
        catch(PDOException $e)
        {
            throw new DatabaseException('Database query wasn\'t executed successfully.', null, $e, $query, $e->getCode(), $e->errorInfo[2]);
        }
        return $result;
    }
    
    /**
     * Metoda provádějící SQL dotaz na databázi a navracející jeden nebo více řádků výsledků jako pole
     * 
     * Vhodné pro SELECT dotazy
     * @param string $query Dotaz pro provedení s otazníky místo parametrů
     * @param array $parameters Pole parametrů, které budou doplněny místo otazníků do dotazu
     * @param bool $all TRUE, pokud se mají navrátit všechny řádky, FALSE pokud pouze první řádek
     * @return array Jednorozměrné nebo dvourozměrné pole obsahující výsledky dotazu
     * @throws DatabaseException V případě selhání dotazu
     */
    public static function fetchQuery(string $query, array $parameters = array(), bool $all = false)
    {
        try
        {
            $statement = self::$connection->prepare($query);
            $statement->execute($parameters);
        }
        catch(PDOException $e)
        {
            throw new DatabaseException('Database query wasn\'t executed successfully.', null, $e, $query, $e->getCode(), $e->errorInfo[2]);
        }

        if ($all)
        {
            return $statement->fetchAll();
        }
        else
        {
            return $statement->fetch();
        }
    }
}