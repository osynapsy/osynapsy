<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Db\Driver;

define('Osynapsy\Core\Driver\DBPDO_NUM', 1);
define('Osynapsy\Core\Driver\DBPDO_ASSOC', 2);
define('Osynapsy\Core\Driver\DBPDO_BOTH', 3);

/**
 * Interface for Db class driver
 *
 * PHP Version 5
 *
 * @category Driver
 * @package  Osynapsy\Core\Driver
 * @author   Pietro Celeste <p.celeste@osynapsy.org>
 * @license  GPL http://www.gnu.org/licenses/gpl-3.0.en.html
 * @link     http://docs.osynapsy.org/ref/InterfaceDbo
 */
interface InterfaceDbo
{
    public function __construct($connectionString);
    
    public function begin();
    
    public function commit();
    
    public function connect();
    
    public function delete($table, array $conditions);
    
    public function execCommand($command, $parameters);
    
    public function execMulti($command, $parameterList);
    
    public function execQuery($query, $parameters = null, $fetchMethod = null);
    
    public function execUnique($query, $parameters = null, $fetchMethod = 'NUM');
    
    public function getColumns();
    
    public function getType();
    
    public function insert($table, array $values);
    
    public function rollback();
    
    public function update($table, array $values, array $conditions);
}
