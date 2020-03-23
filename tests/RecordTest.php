<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Osynapsy\Db\DbFactory;
use Osynapsy\Db\Record\Active as RecordActive;

final class RecordTest extends TestCase
{    
    private function getRecord()
    {
        return new class($this->getConnection()) extends RecordActive {
            public function table()
            {
                return 'tbl_itm';
            }
            
            public function primaryKey()
            {
                return ['id'];
            }
            
            public function fields()
            {
                return ['id'];
            }
        };
    }
    
    private function getConnection()
    {
        $Factory = new DbFactory();
        $Factory->createConnection('mysql:localhost:erp_spinit:webuser:webpassword');
        return $Factory->getConnection(0);
    }
    
    public function testBehavoirOnInit()
    {
        $record = $this->getRecord();
        $this->assertEquals($record->getBehavior(), 'insert');
    }
    
    public function testBehavoirOnFailRetriveActiveRecord()
    {
        $record = $this->getRecord();
        $record->findByAttributes(['id' => 0]);
        $this->assertEquals($record->getBehavior(), 'insert');
    }
}
