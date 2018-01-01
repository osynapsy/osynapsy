<?php
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

namespace Osynapsy\Core\Driver;

define('DBPDO_NUM', 1);
define('DBPDO_ASSOC', 2);
define('DBPDO_BOTH', 3);
/**
 *
 * @author Peter
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
