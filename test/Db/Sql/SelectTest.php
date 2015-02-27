<?php
namespace SporkTest\Db\Sql;

use Spork\Test\TestCase\TestCase;
use Spork\Db\Sql\Select;
use Zend\Db\Sql\Predicate\PredicateSet;

class SelectTest extends TestCase
{
    public function testFactoryFrom()
    {
        $select = Select::factory(array('from' => 'table'));
        $this->assertEquals('table', $select->getRawState(Select::TABLE));
        $this->assertEquals('SELECT "table".* FROM "table"', $select->getSqlString());
    }
    
    public function testFactoryColumns()
    {
        // column list
        $columns = array('alias1' => 'column1', 'column2');
        $select = Select::factory(array(
            'from' => 'table',
            'columns' => $columns));
        $this->assertEquals($columns, $select->getRawState(Select::COLUMNS));
        $this->assertEquals(
            'SELECT "table"."column1" AS "alias1", "table"."column2" AS "column2" FROM "table"', 
            $select->getSqlString());
        
        // column list + prefix table name flag
        $select = Select::factory(array(
            'from' => 'table',
            'columns' => array($columns, false)));
        $this->assertEquals($columns, $select->getRawState(Select::COLUMNS));
        $this->assertEquals(
            'SELECT "column1" AS "alias1", "column2" AS "column2" FROM "table"', 
            $select->getSqlString());
    }
    
    public function testFactoryCombine()
    {
        // select
        $select1 = Select::factory(array('from' => 'table1'));
        $select2 = Select::factory(array('from' => 'table2', 'combine' => $select1));
        $this->assertEquals(
            array(
                'select' => $select1,
                'type' => 'union',
                'modifier' => '',
            ), 
            $select2->getRawState(Select::COMBINE));
        $this->assertEquals(
            '( SELECT "table2".* FROM "table2" ) UNION ( SELECT "table1".* FROM "table1" )', 
            $select2->getSqlString());
        
        // select + type & modifier
        $select1 = Select::factory(array('from' => 'table1'));
        $select2 = Select::factory(array('from' => 'table2', 'combine' => array($select1, 'union', '')));
        $this->assertEquals(
            array(
                'select' => $select1,
                'type' => 'union',
                'modifier' => '',
            ), 
            $select2->getRawState(Select::COMBINE));
        $this->assertEquals(
            '( SELECT "table2".* FROM "table2" ) UNION ( SELECT "table1".* FROM "table1" )', 
            $select2->getSqlString());
    }
    
    public function testFactoryGroup()
    {
        $select = Select::factory(array('from' => 'table', 'group' => 'column1'));
        $this->assertEquals(array('column1'), $select->getRawState(Select::GROUP));
        $this->assertEquals(
            'SELECT "table".* FROM "table" GROUP BY "column1"', 
            $select->getSqlString());
    }
    
    public function testFactoryHaving()
    {
        $having = array('1 + 1 = 2', '1 + 1 != 3');
        $select = Select::factory(array(
            'from' => 'table', 
            'having' => $having));
        $this->assertEquals(
            'SELECT "table".* FROM "table" HAVING 1 + 1 = 2 AND 1 + 1 != 3', 
            $select->getSqlString());
        
        $select = Select::factory(array(
            'from' => 'table',
            'having' => array($having, PredicateSet::OP_OR),
        ));
        // $select->getRawState(HAVING) returns Having instance which is difficult to test
        $this->assertEquals(
            'SELECT "table".* FROM "table" HAVING 1 + 1 = 2 OR 1 + 1 != 3', 
            $select->getSqlString());
    }
    
    public function testFactoryJoin()
    {
        $join = array(
            'name' => 'table2',
            'on' => 'table.column=table2.column',
            'columns' => array('column1', 'column2'),
            'type' => 'outer',
        );
        $select = Select::factory(array(
            'from' => 'table',
            'join' => array_values($join),
        ));
        $this->assertEquals(array($join), $select->getRawState(Select::JOINS));
        $this->assertEquals(
            'SELECT "table".*, "table2"."column1" AS "column1", "table2"."column2" AS "column2" FROM "table" OUTER JOIN "table2" ON "table"."column"="table2"."column"', 
            $select->getSqlString());
    }
    
    public function testFactoryLimit()
    {
        $select = Select::factory(array(
            'from' => 'table',
            'limit' => 1,
        ));
        $this->assertEquals(1, $select->getRawState(Select::LIMIT));
        // getSqlString() generates quote error
    }
    
    public function testFactoryOffset()
    {
        $select = Select::factory(array(
            'from' => 'table',
            'offset' => 1,
        ));
        $this->assertEquals(1, $select->getRawState(Select::OFFSET));
    }
    
    public function testFactoryOrder()
    {
        $select = Select::factory(array(
            'from' => 'table',
            'order' => 'column1',
        ));
        $this->assertEquals(array('column1'), $select->getRawState(Select::ORDER));
        $this->assertEquals(
            'SELECT "table".* FROM "table" ORDER BY "column1" ASC', 
            $select->getSqlString());
    }
    
    public function testFactoryWhere()
    {
        $where = array('1 + 1 = 2', '1 + 1 != 3');
        $select = Select::factory(array(
            'from' => 'table',
            'where' => $where));
        $this->assertEquals(
            'SELECT "table".* FROM "table" WHERE 1 + 1 = 2 AND 1 + 1 != 3',
            $select->getSqlString());
        
        $select = Select::factory(array(
            'from' => 'table',
            'where' => array($where, PredicateSet::OP_OR),
        ));
        // $select->getRawState(WHERE) returns Where instance which is difficult to test
        $this->assertEquals(
            'SELECT "table".* FROM "table" WHERE 1 + 1 = 2 OR 1 + 1 != 3',
            $select->getSqlString());
    }
}