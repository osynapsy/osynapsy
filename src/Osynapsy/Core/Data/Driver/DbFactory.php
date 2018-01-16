<?php
namespace Osynapsy\Core\Data\Driver;

/**
 * Description of DbFactory
 *
 * @author Pietro Celeste <p.celeste@spinit.it>
 */
class DbFactory
{    
    private static $connectionPool = [];
    private static $connectionIndex = [];
    
    /**
     * get a db connection and return
     *
     * @param idx $index
     *
     * @return object
     */
    public static function getConnection($key)
    {
        return array_key_exists($key, self::$connectionPool) ? self::$connectionPool[$key] : false;
    }
    
    /**
     * Exec a db connection and return
     *
     * @param string $connectionString contains parameter to access db (ex.: mysql:database:host:port:username:password)
     *
     * @return object
     */
    public static function connect($connectionString)
    {
        if (array_key_exists($connectionString, self::$connectionIndex)) {
            return self::$connectionPool[self::$connectionIndex[$connectionString]];
        }
        $type = strtok($connectionString, ':');
        switch ($type) {
            case 'oracle':
                $databaseConnection = new DbOci($connectionString);
                break;
            default:
                $databaseConnection = new DbPdo($connectionString);
                break;
        }
        
        //Exec connection
        $res = $databaseConnection->connect();
        
        $currentIndex = count(self::$connectionPool);
        self::$connectionIndex[$connectionString] = $currentIndex;
        self::$connectionPool[$currentIndex] = $databaseConnection;
        
        return $databaseConnection;
    }
}
