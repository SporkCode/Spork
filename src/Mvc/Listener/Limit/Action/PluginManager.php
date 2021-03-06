<?php
/**
 * 
 * Spork Zend Framework 2 Library
 * 
 * @author Chris Schreiber <chris@sporkcode.com>
 */
namespace Spork\Mvc\Listener\Limit\Action;

use Zend\ServiceManager\AbstractPluginManager;

/**
 * Action Plugin Manager
 */
class PluginManager extends AbstractPluginManager
{
    /**
     * List of invokable action plugins
     * 
     * @var array
     */
    protected $invokableClasses = array(
        'accessdeniedtemplate' => 'Spork\Mvc\Listener\Limit\Action\AccessDeniedTemplate',
    );
    
    /**
     * Test the plugin is instance of ActionInterface
     * 
     * @see \Zend\ServiceManager\AbstractPluginManager::validatePlugin()
     * @param ActionInterface $plugin
     * @throws \Exception on plugin not instance of ActionInterface
     */
    public function validatePlugin($plugin)
    {
        if (!$plugin instanceof ActionInterface) {
            throw new \Exception('Plugin must implement Spork\Mvc\Listener\Limit\Storage\StorageInterface');
        }
    }
}