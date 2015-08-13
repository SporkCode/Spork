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
class TestCaseDb extends TestCaseService
{
    /**
     * Database adapter to be used in tests
     * 
     * @var \Zend\Db\Adapter\AdapterInterface
     */
    protected static $dbAdapter;
    
    /**
     * Name of database adapter service
     * 
     * @var string
     */
    protected static $dbAdapterName = 'db';
    
    /**
     * Name of default database. 
     * 
     * @var string
     */
    protected static $dbSchemaDefault;
    
    /**
     * Name of test database. This is null if a test database adapter is not defined
     * and temporary tables are created in the same database.
     * 
     * @var string|null
     */
    protected static $dbSchemaTest;

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
    
    protected function assertTableRowExists($table, $where)
    {
        $this->assertTableRowCount(1, $table, $where);
    }

    /**
     * Initializes database on first run then resets tables on subsequent runs.
     * 
     * @see \Spork\Test\TestCase\TestCaseService::setUp()
     * @throws \Exception on attempt to reset non temporary table
     */
    protected function setUp()
    {
        parent::setUp();
        
        $services = $this->getServiceLocator();

        if (null === self::$dbAdapter) {
            // initialize database connections
            if (isset($GLOBALS['DB_MAIN_SERVICE_NAME'])) {
                self::$dbAdapterName = $GLOBALS['DB_MAIN_SERVICE_NAME'];
            }
            $dbAdapterDefault = $services->get(self::$dbAdapterName);
            self::$dbSchemaDefault = $dbAdapterDefault->getCurrentSchema();
            if (isset($GLOBALS['DB_TEST_SERVICE_NAME'])) {
                self::$dbAdapter = $services->get($GLOBALS['DB_TEST_SERVICE_NAME']);
                self::$dbSchemaTest = self::$dbAdapter->getCurrentSchema();
            } else {
                self::$dbAdapter = $dbAdapterDefault;
            }

            // drop static tables
            if (null !== self::$dbSchemaTest) {
                self::$dbAdapter->query("DROP DATABASE {$this->quoteIdentifier(self::$dbSchemaTest)}", Adapter::QUERY_MODE_EXECUTE);
                self::$dbAdapter->query("CREATE DATABASE {$this->quoteIdentifier(self::$dbSchemaTest)}", Adapter::QUERY_MODE_EXECUTE);
                self::$dbAdapter->query("USE {$this->quoteIdentifier(self::$dbSchemaTest)}", Adapter::QUERY_MODE_EXECUTE);
            }
            
            // create test tables
            foreach ($this->getTables($dbAdapterDefault) as $table) {
                $this->createTestTable($table, $dbAdapterDefault, self::$dbAdapter);
            }
            
            // copy triggers
            if (null !== self::$dbSchemaTest) {
                $triggers = self::$dbAdapter->query(
                    "SHOW TRIGGERS FROM {$this->quoteIdentifier(self::$dbSchemaDefault)}",
                    Adapter::QUERY_MODE_EXECUTE);
                foreach ($triggers as $trigger) {
                    $createTrigger = self::$dbAdapter->query(
                        "SHOW CREATE TRIGGER {$this->quoteIdentifier(self::$dbSchemaDefault)}.{$this->quoteIdentifier($trigger['Trigger'])}", 
                        Adapter::QUERY_MODE_EXECUTE);
                    self::$dbAdapter->query($createTrigger->current()['SQL Original Statement'], Adapter::QUERY_MODE_EXECUTE);
                }
            }
        } else {
            foreach ($this->getTables(self::$dbAdapter) as $table) {
                // make sure temporary tables are temporary
                if (null === self::$dbSchemaTest) {
                    $createTable = $this->getCreateTable($table, self::$dbAdapter);
                    if (strpos($createTable, 'CREATE TEMPORARY TABLE') === false) {
                        throw new \Exception("Table '$table' is not temporary");
                    }
                }
                // empty tables
                $result = self::$dbAdapter->query("truncate table `$table`",
                    Adapter::QUERY_MODE_EXECUTE);
            }
        
            $allowOverride = $services->getAllowOverride();
            $services->setAllowOverride(true)
                ->setService(self::$dbAdapterName, self::$dbAdapter)
                ->setAllowOverride($allowOverride);
        }
        
        $allowOverride = $services->getAllowOverride();
        $services
            ->setAllowOverride(true)
            ->setService(self::$dbAdapterName, self::$dbAdapter)
            ->setAllowOverride($allowOverride);
    }
    
    /**
     * Copies table structure and creates temporary table on top of it.
     * 
     * @param string $table Table name
     * @param Adapter $db
     */
    private function createTestTable($table, Adapter $source, Adapter $dest)
    {
        $createTable = $this->getCreateTable($table, $source);
        if ($source->getCurrentSchema() == $dest->getCurrentSchema()) {
            $createTable = str_replace('CREATE TABLE', 'CREATE TEMPORARY TABLE',
                $createTable);
        }
        $createTable = preg_replace('/ENGINE=\w+/', 'ENGINE=Memory',
            $createTable, 1);
        $createTable = preg_replace('`\stext(\s|,)`i', ' varchar(512)$1',
            $createTable);
        $dest->query($createTable, Adapter::QUERY_MODE_EXECUTE);
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
    
    private function quoteIdentifier($name)
    {
        return self::$dbAdapter->getPlatform()->quoteIdentifier($name);
    }
}