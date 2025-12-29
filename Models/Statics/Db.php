<?php

namespace Poznavacky\Models\Statics;

use Poznavacky\Models\Exceptions\DatabaseException;
use Poznavacky\Models\Logger;
use \PDO;
use \PDOException;

/**
 * PDO databázový wrapper
 * @author Jan Štěch
 */
class Db
{

    private static PDO $connection;
    private static array $settings = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
        PDO::ATTR_EMULATE_PREPARES => false
    );
    
    /**
     * Metoda zakládající spojení s databází a ukládající jej do vlastnosti $connection
     * Všechny parametry mají nastavené základní hodnoty podle konstant této třídy
     * @param string $host Server hostující databázi
     * @param string $username Přihlašovací jméno pro databázi
     * @param string $password Heslo k databázi
     * @param string $database Jméno databáze
     * @return PDO|null Připojení k databázi nebo NULL, pokud se nepodařilo navázat připojení
     */
    public static function connect(string $host = null, string $username = null, string $password = null, string $database = null): ?PDO
    {
        $host = $host ?: Settings::DB_HOST;
        $username = $username ?: Settings::DB_USERNAME;
        $password = $password ?: Settings::DB_PASSWORD;
        $database = $database ?: Settings::DB_NAME;

        try {
            self::$connection = new PDO('mysql:host='.$host.';dbname='.$database, $username, $password,
                self::$settings);
        } catch (PDOException $e) {
            (new Logger())->emergency('K databázi se nebylo možné připojit: {exception}', array('exception' => $e));
            return null;
        }
        return self::$connection;
    }
    
    /**
     * Metoda provádějící SQL dotaz na databázi bez navracení výsledků
     * Pokud zatím nebylo vytvořeno spojení s databází, bude vytvořeno
     * Vhodné pro dotazy jako INSERT, UPDATE a DELETE
     * @param string $query Dotaz pro provedení s otazníky místo parametrů
     * @param array $parameters Pole parametrů, které budou doplněny místo otazníků do dotazu
     * @param bool $returnLastId TRUE, pokud má metoda navracet ID posledního vloženého řádku (vhodné pouze pro INSERT
     *     dotazy), defaultně FALSE (navrátí TRUE v případě, že dotaz neselže)
     * @return bool|int TRUE, pokud dotaz neselhal a pokud je třetí parametr nastaven na FALSE, jinak ID posledního
     *     vloženého řádku
     * @throws DatabaseException V případě selhání dotazu
     */
    public static function executeQuery(string $query, array $parameters = array(), bool $returnLastId = false)
    {
        if (!isset(self::$connection)) {
            self::connect();
        }
        try {
            $statement = self::$connection->prepare($query);
            $result = $statement->execute($parameters);
            
            if ($returnLastId) {
                return self::$connection->lastInsertId();
            }
        } catch (PDOException $e) {
            throw new DatabaseException('Database query wasn\'t executed successfully.', 0, $e, $query,
                $e->getCode(), $e->errorInfo[2]);
        }
        return $result;
    }
    
    /**
     * Metoda provádějící SQL dotaz na databázi a navracející jeden nebo více řádků výsledků jako pole
     * Pokud zatím nebylo vytvořeno spojení s databází, bude vytvořeno
     * Vhodné pro SELECT dotazy
     * @param string $query Dotaz pro provedení s otazníky místo parametrů
     * @param array $parameters Pole parametrů, které budou doplněny místo otazníků do dotazu
     * @param bool $all TRUE, pokud se mají navrátit všechny řádky, FALSE pokud pouze první řádek
     * @return array|boolean Jednorozměrné nebo dvourozměrné pole obsahující výsledky dotazu, FALSE v případě prázdného
     *     výsledku
     * @throws DatabaseException V případě selhání dotazu
     */
    public static function fetchQuery(string $query, array $parameters = array(), bool $all = false)
    {
        if (!isset(self::$connection)) {
            self::connect();
        }
        try {
            $statement = self::$connection->prepare($query);
            $statement->execute($parameters);
        } catch (PDOException $e) {
            throw new DatabaseException('Database query wasn\'t executed successfully.', 0, $e, $query,
                $e->getCode(), $e->errorInfo[2]);
        }
        
        if ($statement->rowCount() === 0) {
            return false;
        }
        if ($all) {
            return $statement->fetchAll();
        } else {
            return $statement->fetch();
        }
    }
    
    /**
     * Metoda provádějící nepřipravený SQL dotaz na databázi a navracející jeho výsledek
     * Pokud zatím nebylo vytvořeno spojení s databází, bude vytvořeno
     * POZOR! Nesmí být využíváno při jakékoliv akci, kterou mohou vyvolat nepověření uživatelé - může dojít k SQL
     * injekci
     * @param string $query SQL dotaz k vykonání, s vloženými proměnnými
     * @return boolean|array TRUE, v případě úspěšného vykonání dotazu bez navrácení výsledků; FALSE v případě selhání
     *     dotazu; Jednorozměrné nebo dvojrozměrné pole obsahující všechny výsledky dotazu, pokud nějaké navrátil
     */
    public static function unpreparedQuery(string $query)
    {
        if (!isset(self::$connection)) {
            self::connect();
        }
        try {
            $result = self::$connection->query($query);
            if (!$result) {
                throw new PDOException('Failed to execute query.');
            }
        } catch (PDOException $e) {
            return false;
        }
        
        $returnedRows = $result->fetchAll();
        if (count($returnedRows) === 0) {
            return true;
        } else {
            return $returnedRows;
        }
    }
}

