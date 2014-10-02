<?php
/**
 * 
 * Spork Zend Framework 2 Library
 * 
 * @author Chris Schreiber <chris@sporkcode.com>
 */
namespace Spork\Test\TestCase;

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway;
use Zend\Db\Sql\Expression;

/**
 * PHPUnit Test Case that setups up environment for tests that require 
 * database access. 
 * 
 * NOTE: This was written for MySQL and probably won't work with other databases.
 * 
 * Database is initialized on first use by coping the structure of existing
 * tables and creating temporary tables on top of them. The temporary tables
 * are then truncated and reused on additional tests to speed up performance.
 * Temporary tables are changed to use the Memory engine when possible to
 * speed up performance.   
 */
class Db extends Service
{
    /**
     * Has database been initialized
     * 
     * @var boolean
     */
    protected static $dbInitialized = false;

    /**
     * Name of database adapter service
     * 
     * @var string
     */
    protected $dbAdapterServiceName = 'db';

    /**
     * Assert that a table has specified number for rows
     * 
     * @param integer $count
     * @param string|TableGateway $table
     * @param string|array|Closure|PredicateInterface $where
     * @throws \Exception on $table not a table name or TableGateway instance
     */
    protected function assertTableRowCount($count, $table, $where = null)
    {
        if (is_string($table)) {
            $table = new TableGateway\TableGateway($table, 
                $this->getServices()->get('db'));
        }
        if (!$table instanceof TableGateway\TableGatewayInterface) {
            throw new \Exception(
                "$table must be a table name or TableGateway object");
        }
        
        $select = $table->getSql()->select()
            ->columns(array('count' => new Expression('count(*)')
        ));
        if (null !== $where) {
            $select->where($where);
        }
        $actual = $table->selectWith($select)->current()->count;
        parent::assertEquals($count, $actual);
    }

    /**
     * Initializes database on first run then resets tables on subsequent runs.
     * 
     * @see \Spork\Test\TestCase\Service::setUp()
     * @throws \Exception on attempt to reset non temporary table
     */
    protected function setUp()
    {
        parent::setUp();
        
        $db = $this->getServices()->get($this->dbAdapterServiceName);
        
        if (false === self::$dbInitialized) {
            self::$dbInitialized = true;
            
            foreach ($this->getTables($db) as $table) {
                $this->createTemporaryTable($table, $db);
            }
        } else {
            foreach ($this->getTables($db) as $table) {
                $createTable = $this->getCreateTable($table, $db);
                if (strpos($createTable, 'CREATE TEMPORARY TABLE') == 0) {
                    $result = $db->query("truncate table `$table`", 
                        Adapter::QUERY_MODE_EXECUTE);
                } else {
                    throw new \Exception("Table '$table' is not temporary");
                }
            }
        }
    }
    
    /**
     * Copies table structure and creates temporary table on top of it.
     * 
     * @param string $table Table name
     * @param Adapter $db
     */
    private function createTemporaryTable($table, Adapter $db)
    {
        $createTable = $this->getCreateTable($table, $db);
        $createTable = str_replace('CREATE TABLE', 'CREATE TEMPORARY TABLE',
            $createTable);
        $createTable = preg_replace('/ENGINE=\w+/', 'ENGINE=Memory',
            $createTable, 1);
        $createTable = preg_replace('`\stext(\s|,)`i', ' varchar(512)$1',
            $createTable);
        $db->query($createTable, Adapter::QUERY_MODE_EXECUTE);
    }

    /**
     * Gets table structure
     * 
     * @param string $table Table name
     * @param Adapter $db
     * @return mixed
     */
    private function getCreateTable($table, Adapter $db)
    {
        $result = (array) $db->query("SHOW CREATE TABLE `$table`", 
            Adapter::QUERY_MODE_EXECUTE)->current();
        $createTable = array_pop($result);
        return $createTable;
    }

    /**
     * Get list of tables in current schema
     * 
     * @param Adapter $db
     * @return array
     */
    private function getTables(Adapter $db)
    {
        $tables = $db->query("show tables", 
            Adapter::QUERY_MODE_EXECUTE)->toArray();
        $tables = array_map('array_pop', $tables);
        return $tables;
    }
}