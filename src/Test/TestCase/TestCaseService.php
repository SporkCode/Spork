<?php
/**
 * 
 * Spork Zend Framework 2 Library
 * 
 * @author Chris Schreiber <chris@sporkcode.com>
 */
namespace Spork\Test\TestCase;

use Zend\ServiceManager\ServiceManager;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\View\Renderer\PhpRenderer;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManagerAwareInterface;

/**
 * PHPUnit Test Case for tests that need a Service Manager
 */
abstract class TestCaseService extends TestCase
{
    use \Spork\ServiceManager\ServicesAwareTrait;
    
    /**
     * Should services be cloned from prototype or created from scratch. This
     * is experimental and should be used with caution.
     * 
     * @var boolean
     */
    protected $cloneServices = false;
    
    /**
     * Service Manager Prototype
     * 
     * @var \Zend\ServiceManager\ServiceManager
     */
    protected static $serviceManagerPrototype;
    
    /**
     * Initialize a Service Manager 
     */
    protected function setUp()
    {
        parent::setUp();
        
        if ($this->cloneServices) {
            if (null === self::$serviceManagerPrototype) {
                $this->initializePrototype();
            }
            
            $this->services = clone self::$serviceManagerPrototype;
        } else {
            $config = require 'config/application.config.php';
            $this->services = new ServiceManager(
                new ServiceManagerConfig(
                    isset($config['service_manager']) ?
                    $config['service_manager'] : array()));
            $this->services->setService('ApplicationConfig', $config);
            $this->services->get('ModuleManager')->loadModules();
        }
    }

    /**
     * Remove Service Manager 
     */
    protected function tearDown()
    {
        parent::tearDown();
        
        $this->services = null;
        //$this->services->clean();
        
    }
    
    /**
     * Create a Service Manager Prototype object
     */
    protected function initializePrototype()
    {
        $config = require 'config/application.config.php';
        $serviceManager = new ServiceManager(
                new ServiceManagerConfig(
                        isset($config['service_manager']) ?
                        $config['service_manager'] : array()));
        $serviceManager->setService('ApplicationConfig', $config);
        $serviceManager->get('ModuleManager')->loadModules();
        self::$serviceManagerPrototype = $serviceManager;
    }
    
    /*
    protected function persistServices(ServiceLocatorInterface $serviceManager)
    {
        $ref = new \ReflectionClass($serviceManager);
        $refInstances = $ref->getProperty('instances');
        $refInstances->setAccessible(true);
        foreach ($refInstances->getValue($serviceManager) as $name => $instance) {
            if (is_object($instance) && $serviceManager !== $instance) {
                self::$services[$name] =  clone $instance;
            } else {
                self::$services[$name] = $instance;
            }
        }
    }
    
    protected function resetServices(ServiceLocatorInterface $serviceManager)
    {
        $ref = new \ReflectionClass($serviceManager);
        $refInstances = $ref->getProperty('instances');
        $refInstances->setAccessible(true);
        $instances = array();
        foreach (self::$services as $name => $instance) {
            if ($instance instanceof ServiceManagerAwareInterface) {
                $instance->setServiceManager($serviceManager);
            }
            if (is_object($instance) && $serviceManager !== $instance) {
                $instances[$name] = clone $instance;
            } else {
                $instances[$name] = $instance;
            }
        }
        $refInstances->setValue($serviceManager, $instances);
        
        $refInitializers = $ref->getProperty('initializers');
        $refInitializers->setAccessible(true);
        $refInitializers->setValue($serviceManager, array());
        $serviceManager->addInitializer(function ($instance) use ($serviceManager) {
            if ($instance instanceof EventManagerAwareInterface) {
                if ($instance->getEventManager() instanceof EventManagerInterface) {
                    $instance->getEventManager()->setSharedManager(
                        $serviceManager->get('SharedEventManager')
                    );
                } else {
                    $instance->setEventManager($serviceManager->get('EventManager'));
                }
            }
        });

        $serviceManager->addInitializer(function ($instance) use ($serviceManager) {
            if ($instance instanceof ServiceManagerAwareInterface) {
                $instance->setServiceManager($serviceManager);
            }
        });

        $serviceManager->addInitializer(function ($instance) use ($serviceManager) {
            if ($instance instanceof ServiceLocatorAwareInterface) {
                $instance->setServiceLocator($serviceManager);
            }
        });
    }
    */
}