<?php
/**
 * 
 * Spork Zend Framework 2 Library
 * 
 * @author Chris Schreiber <chris@sporkcode.com>
 */
namespace Spork\Mvc\Listener\Limit\Storage;

use Zend\ServiceManager\AbstractPluginManager;

/**
 * Manages Storage classes for Limit
 */
class PluginManager extends AbstractPluginManager
{
    /**
     * List of invokable plugins
     * @var array
     */
    protected $invokableClasses = array(
        'db' => 'Spork\Mvc\Listener\Limit\Storage\Db',
    );
    
    /**
     * Checks that plugin is a StorageInterface
     * 
     * @see \Zend\ServiceManager\AbstractPluginManager::validatePlugin()
     * @param StorageInterface $plugin
     * @throws \Exception on plugin not a StorageInterface
     */
    public function validatePlugin($plugin)
    {
        if (!$plugin instanceof StorageInterface) {
            throw new \Exception('Plugin must implement Spork\Mvc\Listener\Limit\Storage\StorageInterface');
        }
    }
}