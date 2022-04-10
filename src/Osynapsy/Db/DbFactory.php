<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Db;

use Osynapsy\Db\Driver\DbOci;
use Osynapsy\Db\Driver\DbPdo;

/**
 * This class build db connection and store it in connectionPool repo.
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
     * @param idx $key
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
     * @param string $connectionString contains parameter to access db (ex.: mysql:database:host:username:password:port)
     *
     * @return object
     */
    public function createConnection($connectionString, $idx = null)
    {
        if (array_key_exists($connectionString, $this->connectionIndex)) {
            return $this->connectionPool[$this->connectionIndex[$connectionString]];
        }
        $databaseConnection = strtok($connectionString, ':') === 'oracle' ? new DbOci($connectionString) : new DbPdo($connectionString);
        $currentIndex = $idx ?? count($this->connectionPool);
        $this->connectionIndex[$connectionString] = $currentIndex;
        $this->connectionPool[$currentIndex] = $databaseConnection;
        return $databaseConnection;
    }
}
