<?php
/**
 * 
 * Spork Zend Framework 2 Library
 * 
 * @author Chris Schreiber <chris@sporkcode.com>
 */

namespace Spork\Mvc\Listener\Limit\Storage;

use Zend\Db\Adapter\ParameterContainer;
use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\Http\PhpEnvironment\Request;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

use Spork\DateTime\DateInterval;
use Spork\Mvc\Listener\Limit\Limit;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Adapter\Adapter;

/**
 * Stores limit data in database table
 * 
 * Example MySQL table
 * CREATE TABLE IF NOT EXISTS `limit` (
 *   `type` varchar(32) NOT NULL,
 *   `ip` varchar(45) NOT NULL,
 *   `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 *   KEY `type` (`type`,`ip`),
 *   KEY `timestamp` (`timestamp`)
 * ) ENGINE=InnoDB DEFAULT CHARSET=ascii;
 * 
 */
class Db implements StorageInterface, ServiceLocatorAwareInterface
{
    /**
     * Should storage be cleaned when it is checked
     * @var boolean
     */
    protected $cleanOnCheck = true;
    
    /**
     * Remove data after this amount of time.
     * @var DateInterval | string
     */
    protected $cleanInterval = 'P1D';
    
    /**
     * Database adapter
     * @var \Zend\Db\Adapter\Adapter
     */
    protected $dbAdapter = 'db';
    
    /**
     * Name of database table
     * @var string
     */
    protected $table = 'limit';
    
    /**
     * Name of type column
     * @var string
     */
    protected $typeColumn = 'type';
    
    /**
     * Name of timestamp column
     * @var string
     */
    protected $timestampColumn = 'timestamp';

    /**
     * Name of ip column
     * @var string
     */
    protected $ipColumn = 'ip';
    
    /**
     * Configure instance
     * 
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        foreach ($options as $key => $value) {
            switch ($key) {
                case 'dbAdapter':
                    $this->setDbAdapter($value);
                    break;
                case 'table':
                    $this->setTable($value);
                    break;
                case 'typeColumn':
                    $this->setTypeColumn($value);
                    break;
                case 'timestampColumn':
                    $this->setTimestampColumn($value);
                    break;
                case 'ipColumn':
                    $this->setIpColumn($value);
                    break;
            }
        }
    }
    
    /**
     * Use Service Manager to inject database adapter
     * 
     * @see \Zend\ServiceManager\ServiceLocatorAwareInterface::setServiceLocator()
     * @param ServiceLocatorInterface $serviceManager
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceManager)
    {
        if (!$this->dbAdapter instanceof DbAdapter) {
            $this->dbAdapter = $serviceManager->getServiceLocator()->get($this->dbAdapter);
        }
    }
    
    /**
     * Not Implemented
     * 
     * @see \Zend\ServiceManager\ServiceLocatorAwareInterface::getServiceLocator()
     * @throws \Exception
     */
    public function getServiceLocator()
    {
        throw new \Exception('Not implemented');
    }
    
    /**
     * Get clean on check flag
     * 
     * @return boolean
     */
    public function getCleanOnCheck()
    {
        return $this->cleanOnCheck;
    }
    
    /**
     * Set clean on check flag
     * 
     * @param boolean $flag
     */
    public function setCleanOnCheck($flag)
    {
        $this->cleanOnCheck = (boolean) $flag;
    }
    
    /**
     * Get clean interval
     * 
     * @return \Spork\DateTime\DateInterval
     */
    public function getCleanInterval()
    {
        if (!$this->cleanInterval instanceof DateInterval) {
            $this->cleanInterval = new DateInterval($this->cleanInterval);
        }
        return $this->cleanInterval;
    }
    
    /**
     * Set interval
     * 
     * @param unknown $interval
     */
    public function setCleanInterval($interval)
    {
        $this->cleanInterval = $interval;
    }
    
    /**
     * Get database adapter
     * 
     * @return \Zend\Db\Adapter\Adapter
     */
    public function getDbAdapter()
    {
        return $this->dbAdapter;
    }
    
    /**
     * Set database adapter or service name
     * 
     * @param \Zend\Db\Adapter\Adapter|string $dbAdapter
     * @return \Spork\Mvc\Listener\Limit\Storage\Db
     */
    public function setDbAdapter($dbAdapter)
    {
        $this->dbAdapter = $dbAdapter;
        return $this;
    }
    
    /**
     * Get table name
     * 
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }
    
    /**
     * Set table name
     * 
     * @param string $table
     * @return \Spork\Mvc\Listener\Limit\Storage\Db
     */
    public function setTable($table)
    {
        $this->table = $table;
        return $this;
    }
    
    /**
     * Get type column name
     * 
     * @return string
     */
    public function getTypeColumn()
    {
        return $this->typeColumn;
    }
    
    /**
     * Set type column name
     * 
     * @param string $typeColumn
     * @return \Spork\Mvc\Listener\Limit\Storage\Db
     */
    public function setTypeColumn($typeColumn)
    {
        $this->typeColumn = $typeColumn;
        return $this;
    }
    
    /**
     * Get timestamp column name
     * 
     * @return string
     */
    public function getTimestampColumn()
    {
        return $this->timestampColumn;
    }
    
    /**
     * Set timestamp column name
     * 
     * @param string $timestampColumn
     * @return \Spork\Mvc\Listener\Limit\Storage\Db
     */
    public function setTimestampColumn($timestampColumn)
    {
        $this->timestampColumn = $timestampColumn;
        return $this;
    }

    /**
     * Set IP column name
     * 
     * @return string
     */
    public function getIpColumn()
    {
        return $this->ipColumn;
    }
    
    /**
     * Get IP column name
     * 
     * @param string $ipColumn
     * @return \Spork\Mvc\Listener\Limit\Storage\Db
     */
    public function setIpColumn($ipColumn)
    {
        $this->ipColumn = $ipColumn;
        return $this;
    }
    
    /**
     * Add record to database
     * 
     * @see \Spork\Mvc\Listener\Limit\Storage\StorageInterface::increment()
     * @param unknown $ip
     * @param unknown $type
     */
    public function increment($ip, $type)
    {
        $this->assertReady();
        
        $sql = sprintf("INSERT INTO %s SET %s=:type, %s=:ip",
            $this->dbAdapter->platform->quoteIdentifier($this->table),
            $this->dbAdapter->platform->quoteIdentifier($this->typeColumn),
            $this->dbAdapter->platform->quoteIdentifier($this->ipColumn));
        
        $parameters = array(
            'type' => $type,
            'ip' => $ip,
        );
        
        $this->dbAdapter->query($sql, $parameters);
    }
    
    /**
     * Check if the number or records in the database exceeds the set limit
     * 
     * @see \Spork\Mvc\Listener\Limit\Storage\StorageInterface::check()
     * @param string $ip
     * @param Limit $limit
     * @return boolean
     */
    public function check($ip, Limit $limit)
    {
        $this->assertReady();
        
        if ($this->cleanOnCheck) {
            $this->clean();
        }
        
        $sql = sprintf("SELECT count(*) as count FROM %s WHERE %s=:ip AND %s=:type AND %s >= NOW() - INTERVAL %d SECOND",
            $this->dbAdapter->platform->quoteIdentifier($this->table),
            $this->dbAdapter->platform->quoteIdentifier($this->ipColumn),
            $this->dbAdapter->platform->quoteIdentifier($this->typeColumn),
            $this->dbAdapter->platform->quoteIdentifier($this->timestampColumn),
            $limit->getInterval()->toSeconds());
        
        $parameters = array(
            'ip' => $ip,
            'type' => $limit->getName(),
        );
        
        $result = $this->dbAdapter->query($sql, $parameters);
        $count = $result->current()->count;
        return $count >= $limit->getLimit();
    }
    
    /**
     * Remove expired records from database
     * 
     * @see \Spork\Mvc\Listener\Limit\Storage\StorageInterface::clean()
     */
    public function clean()
    {
        $this->assertReady();
        
        $sql = sprintf("DELETE FROM %s WHERE %s < NOW() - INTERVAL %d SECOND",
            $this->dbAdapter->platform->quoteIdentifier($this->table),
            $this->dbAdapter->platform->quoteIdentifier($this->timestampColumn),
            $this->getCleanInterval()->toSeconds());
        
        $this->dbAdapter->query($sql, DbAdapter::QUERY_MODE_EXECUTE);
    }
    
    /**
     * Assert database adapter is ready
     * 
     * @throws \Exception on database adapter not ready
     */
    protected function assertReady()
    {
        if (!$this->dbAdapter instanceof DbAdapter) {
            throw new \Exception('Database adapter not set');
        }
    }
}