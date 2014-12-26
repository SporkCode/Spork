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
     * Has database been initialized
     * 
     * @var boolean
     */
    protected static $dbInitialized = false;
    
    /**
     * Database adapter
     * 
     * @var \Zend\Db\Adapter\AdapterInterface
     */
    protected static $dbAdapter;

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
     * @see \Spork\Test\TestCase\TestCaseService::setUp()
     * @throws \Exception on attempt to reset non temporary table
     */
    protected function setUp()
    {
        parent::setUp();

        $services = $this->getServices();
        $dbMainServiceName = isset($GLOBALS['DB_MAIN_SERVICE_NAME']) ? $GLOBALS['DB_MAIN_SERVICE_NAME'] : 'db';
        $dbTestServiceName = isset($GLOBALS['DB_TEST_SERVICE_NAME']) ? $GLOBALS['DB_TEST_SERVICE_NAME'] : null;
        $source = $services->get($dbMainServiceName);
        $dest = null === $dbTestServiceName ? $services->get($dbMainServiceName) : $services->get($dbTestServiceName);
        
        if (null === self::$dbAdapter) {
            self::$dbAdapter = $dest;
            
            $this->initializeDatabase($source, $dest);
        } else {
            foreach ($this->getTables(self::$dbAdapter) as $table) {
                if ($source->getCurrentSchema() == $dest->getCurrentSchema()) {
                    $createTable = $this->getCreateTable($table, self::$dbAdapter);
                    if (strpos($createTable, 'CREATE TEMPORARY TABLE') === false) {
                        throw new \Exception("Table '$table' is not temporary");
                    }
                }
                $result = self::$dbAdapter->query("truncate table `$table`",
                        Adapter::QUERY_MODE_EXECUTE);
            }
        }
        
        $allowOverride = $services->getAllowOverride();
        $services->setAllowOverride(true)
                ->setService($dbMainServiceName, self::$dbAdapter)
                ->setAllowOverride($allowOverride);
    }
    
    private function initializeDatabase(Adapter $source, Adapter $dest)
    {
        if ($source->getCurrentSchema() != $dest->getCurrentSchema()) {
            $destDatabase = $dest->platform->quoteIdentifier($dest->getCurrentSchema());
            $dest->query("DROP DATABASE $destDatabase", Adapter::QUERY_MODE_EXECUTE);
            $dest->query("CREATE DATABASE $destDatabase", Adapter::QUERY_MODE_EXECUTE);
            $dest->query("USE $destDatabase", Adapter::QUERY_MODE_EXECUTE);
        }
        
        foreach ($this->getTables($source) as $table) {
            $this->createTestTable($table, $source, $dest);
        }
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
//         } else {
//             if (preg_match('"^\s*CREATE\s*(TEMPORARY)?\s*TABLE\s*(IF\s*NOT\s*EXISTS)?\s*`?(\w+)`?\s"i', 
//                     $createTable, $matches)) {
//                 $createTable = "DROP TABLE IF EXISTS `{$matches[3]}`; \n" . $createTable;
//             } else {
//                 throw new \Exception("Cannot parse create table statement");
//             }
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
}