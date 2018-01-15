<?php
namespace Osynapsy\Core\Data\Driver;

/**
 * Description of DbFactory
 *
 * @author Pietro Celeste <p.celeste@spinit.it>
 */
class DbFactory
{    
    private $connectionPool = [];
    
    /**
     * Exec a db connection and return
     *
     * @param string $connectionString contains parameter to access db (ex.: mysql:database:host:port:username:password)
     *
     * @return object
     */
    public static function connection($connectionString)
    {
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
        if ($databaseConnection->connect()) {
            return self::$connectionPool[$connectionString] = $databaseConnection;
        }
        return false;
    }
}
