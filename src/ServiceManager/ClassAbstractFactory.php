<?php
/**
 * 
 * Spork Zend Framework 2 Library
 * 
 * @author Chris Schreiber <chris@sporkcode.com>
 */
namespace Spork\ServiceManager;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Generic abstract factory that will create and configure an instance for any 
 * service as long as the service name has an entry in the configuration and the 
 * configuration includes a class name.
 */
class ClassAbstractFactory implements AbstractFactoryInterface
{
    /**
     * Test if configuration exists and has class field
     * 
     * @see \Zend\ServiceManager\AbstractFactoryInterface::canCreateServiceWithName()
     * @param ServiceLocatorInterface $serviceLocator
     * @param string $name
     * @param string $requestedName
     * @return boolean
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $config = $serviceLocator->get('config');
        $class = $this->getClass($config, $requestedName);
        return $class && class_exists($class);
    }
    
    /**
     * Create and configure class by creating new instance of class from
     * configuration
     * 
     * @see \Zend\ServiceManager\AbstractFactoryInterface::createServiceWithName()
     * @param ServiceLocatorInterface $serviceLocator
     * @param string $name
     * @param string $requestedName
     * @return mixed
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $config = $serviceLocator->get('config');
        $class = $this->getClass($config, $requestedName);
        return new $class($config[$requestedName]);
    }
    
    /**
     * Look for class name in configuration
     * 
     * @param array $config
     * @param string $name
     * @return boolean
     */
    protected function getClass($config, $name)
    {
        if (array_key_exists($name, $config) 
                && is_array($config[$name])
                && array_key_exists('class', $config[$name]) 
                && is_scalar($config[$name]['class'])) {
            return $config[$name]['class'];
        }
        
        return false;
    }
}