<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Osynapsy\Db\DbFactory;
use Osynapsy\Db\Record\Active as RecordActive;

final class RecordTest extends TestCase
{
    private function getRecord()
    {
        return new class($this->getConnection()) extends RecordActive
        {
            public function table()
            {
                return 'tbl_test';
            }

            public function primaryKey()
            {
                return ['id'];
            }

            public function fields()
            {
                return ['id','label'];
            }
        };
    }

    private function getConnection()
    {
        $Factory = new DbFactory();
        $Factory->createConnection('sqlite::memory:');
        $Factory->getConnection(0)->execCommand("CREATE TABLE tbl_test (id INTEGER PRIMARY KEY AUTOINCREMENT, label varchar(20)); ");
        $Factory->getConnection(0)->insert('tbl_test', ['label' => 'test1']);
        $Factory->getConnection(0)->insert('tbl_test', ['label' => 'test2']);
        return $Factory->getConnection(0);
    }

    public function testBehavoirOnInit()
    {
        $record = $this->getRecord();
        $this->assertEquals($record->getBehavior(), 'insert');
    }

    public function testBehavoirOnSuccessRetriveActiveRecord()
    {
        $record = $this->getRecord();
        $record->findByAttributes(['id' => '1']);
        $this->assertEquals($record->getBehavior(), 'update');
    }

    public function testBehavoirOnFailRetriveActiveRecord()
    {
        $record = $this->getRecord();
        $record->findByAttributes(['id' => '4']);
        $this->assertEquals($record->getBehavior(), 'insert');
    }
}
