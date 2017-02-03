<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Osynapsy\Core\Driver;

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
