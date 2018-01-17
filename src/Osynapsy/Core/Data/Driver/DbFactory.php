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
    private $connectionIndex = [];
    
    /**
     * get a db connection and return
     *
     * @param idx $index
     *
     * @return object
     */
    public function getConnection($key)
    {
        return array_key_exists($key, $this->connectionPool) ? $this->connectionPool[$key] : false;
    }
    
    /**
     * Exec a db connection and return
     *
     * @param string $connectionString contains parameter to access db (ex.: mysql:database:host:port:username:password)
     *
     * @return object
     */
    public function buildConnection($connectionString)
    {
        if (array_key_exists($connectionString, $this->connectionIndex)) {
            return $this->connectionPool[$this->connectionIndex[$connectionString]];
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
        
        $currentIndex = count($this->connectionPool);
        $this->connectionIndex[$connectionString] = $currentIndex;
        $this->connectionPool[$currentIndex] = $databaseConnection;
        
        return $databaseConnection;
    }
}
