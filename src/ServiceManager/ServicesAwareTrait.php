<?php
/**
 * 
 * Spork Zend Framework 2 Library
 * 
 * @author Chris Schreiber <chris@sporkcode.com>
 */
namespace Spork\ServiceManager;

use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Provides a concise interface to access a service manager while maintaining compatibility
 * with Zend\ServiceManager\ServiceLocatorAwareInterface and 
 * Zend\ServiceManager\ServiceManagerAwareInterface  
 */
trait ServicesAwareTrait
{
    /**
     * Service Manager
     * 
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    protected $services;
    
    /**
     * Unset services 
     */
    public function __destruct()
    {
        if (isset($this->services)) {
            unset($this->services);
        }
    }
    
    /**
     * Get Service Manager
     * 
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    public function getServices()
    {
        return $this->services;
    }
    
    /**
     * Set Service Manager
     * 
     * @param ServiceLocatorInterface $services
     */
    public function setServices(ServiceLocatorInterface $services)
    {
        $this->services = $services;
    }
    
    /**
     * Provides compatibility ServiceLocatorAwareInterface
     * 
     * @deprecated
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->getServices();
    }
    
    /**
     * Provides compatibility ServiceLocatorAwareInterface
     * @deprecated
     * @param ServiceLocatorInterface $services
     */
    public function setServiceLocator(ServiceLocatorInterface $services)
    {
        $this->setServices($services);
    }
    
    /**
     * Provides compatibility ServiceManagerAwareInterface
     * @deprecated
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    public function getServiceManager()
    {
        return $this->getServices();
    }
    
    /**
     * Provides compatibility ServiceManagerAwareInterface
     * @deprecated
     * @param ServiceLocatorInterface $services
     */
    public function setServiceManager(ServiceLocatorInterface $services)
    {
        $this->setServices($services);
    }
}