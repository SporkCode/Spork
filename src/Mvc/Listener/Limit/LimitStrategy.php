<?php
/**
 * 
 * Spork Zend Framework 2 Library
 * 
 * @author Chris Schreiber <chris@sporkcode.com>
 */
namespace Spork\Mvc\Listener\Limit;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Http\PhpEnvironment\RemoteAddress;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Keeps track of when a client does something suspicious and takes action when
 * a client has exceeded the threshold for a type of event. Each type of event
 * has its own threshold limit and interval and actions.
 */
class LimitStrategy extends AbstractPlugin 
        implements ListenerAggregateInterface, ServiceLocatorAwareInterface
{
    const CONFIG_KEY = 'limit_strategy';

    /**
     * Actions Plugin Manager
     * 
     * @var \Zend\ServiceManager\AbstractPluginManager
     */
    protected $actionPlugins;
    
    /**
     * List of Limit instances
     * 
     * @var array
     */
    protected $limits = array();

    /**
     * Event listeners
     * 
     * @var array
     */
    protected $listeners = array();
    
    /**
     * Remote Address
     * 
     * @var \Zend\Http\PhpEnvironment\RemoteAddress
     */
    protected $remoteAddress;
    
    /**
     * Name of controller plugin
     * 
     * @var string
     */
    protected $pluginName = 'limit';

    /**
     * Storage Plugin Manager
     * 
     * @var \Zend\ServiceManager\AbstractPluginManager
     */
    protected $storagePlugins;
    
    /**
     * Storage instance
     * 
     * @var \Spork\Mvc\Listener\Limit\Storage\StorageInterface
     */
    protected $storage;

    /**
     * Initialize strategy
     * 
     * @param array $options
     */
    public function __construct(array $options = null)
    {
        $this->remoteAddress = new RemoteAddress();
        if (null !== $options) {
            $this->configure($options);
        }
    }

    /**
     * Attach event listeners
     * 
     * @param EventManagerInterface $events
     * @see \Zend\EventManager\ListenerAggregateInterface::attach()
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_BOOTSTRAP,
            array(
                $this,
                'initialize'
            ), 1001);
        $this->listeners[] = $events->attach(MvcEvent::EVENT_BOOTSTRAP,
            array(
                $this,
                'check'
            ), 1000);
    }
    
    /**
     * Detach event listeners
     * 
     * @param EventManagerInterface $events
     * @see \Zend\EventManager\ListenerAggregateInterface::detach()
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $listener) {
            $events->detach($listener);
        }
    }
    
    /**
     * Get service locator
     * 
     * @see \Zend\ServiceManager\ServiceLocatorAwareInterface::getServiceLocator()
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->getActionPlugins()->getServiceLocator();
    }
    
    /**
     * Set service locator
     * 
     * @see \Zend\ServiceManager\ServiceLocatorAwareInterface::setServiceLocator()
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->getActionPlugins()->setServiceLocator($serviceLocator);
        $this->getStoragePlugins()->setServiceLocator($serviceLocator);
    }
    
    /**
     * Get action plugin manager
     * 
     * @return \Zend\ServiceManager\AbstractPluginManager
     */
    public function getActionPlugins()
    {
        if (null === $this->actionPlugins) {
            $this->setActionPlugins(new Action\PluginManager());
        }
        return $this->actionPlugins;
    }
    
    /**
     * Set action plugin manager
     * 
     * @param AbstractPluginManager $actionPlugins
     */
    public function setActionPlugins(AbstractPluginManager $actionPlugins)
    {
        $this->actionPlugins = $actionPlugins;
    }

    /**
     * Get limits
     * 
     * @return array
     */
    public function getLimits()
    {
        return $this->limits;
    }

    /**
     * Set limits
     * 
     * @param array $limits
     */
    public function setLimits(array $limits)
    {
        $this->limits = array();
        foreach ($limits as $limit) {
            $this->setLimit($limit);
        }
    }

    /**
     * Set a limit
     * 
     * @param Limit|array $limit
     * @throws \Exception
     */
    public function setLimit($limit)
    {
        if (is_array($limit)) {
            $spec = $limit;
            if (!isset($spec['name'])) {
                throw new \Exception("Cannot create Limit: Name missing from configuration.");
            }
            $name = $spec['name'];
            $limit = isset($spec['limit']) ? $spec['limit'] : null;
            $interval = isset($spec['interval']) ? $spec['interval'] : null;
            $actions = isset($spec['actions']) ? $spec['actions'] : array();
            
            $limit = new Limit($name, $limit, $interval);
            
            foreach ($actions as $action) {
                if (is_string($action)) {
                    $action = $this->getActionPlugins()->get($action);
                } elseif (is_array($action)) {
                    if (!isset($action['name'])) {
                        throw new \Exception('Cannot create Action: Name missing from configuration.');
                    }
                    $action = $this->getActionPlugins()->get($action['name'], $action);
                }
                
                if (!$action instanceof Action\ActionInterface) {
                    throw new \Exception("Action must be instance of Spork\Mvc\Listener\Limit\Action\ActionInterface");
                }
                
                $limit->addAction($action);
            }
        }
        
        if (!$limit instanceof Limit) {
            throw new \Exception("Limit must be instance of Spork\Mvc\Listener\Limit\Limit");
        }
        
        $this->limits[$limit->getName()] = $limit;
    }
    
    /**
     * Get controller plugin name
     * 
     * @return string
     */
    public function getPluginName()
    {
        return $this->pluginName;
    }
    
    /**
     * Set controller plugin name
     * 
     * @param string $name
     * @return \Spork\Mvc\Listener\Limit\LimitStrategy
     */
    public function setPluginName($name)
    {
        $this->pluginName = $name;
        return $this;
    }

    /**
     * Get storage instance
     * 
     * @return \Spork\Mvc\Listener\Limit\Storage\StorageInterface
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * Set storage instance
     * 
     * @param StorageInterface|string|array $storage
     * @return \Spork\Mvc\Listener\Limit\LimitStrategy
     * @throws \Exception on invalid type
     */
    public function setStorage($storage)
    {
        if (is_string($storage)) {
            $storage = $this->getStoragePlugins()->get($storage);
        } elseif (is_array($storage)) {
            if (!isset($storage['name'])) {
                throw new \Exception('Invalid Storage configuration: Name required.');
            }
            $storage = $this->getStoragePlugins()->get($storage['name'], $storage);
        }
        
        if (!$storage instanceof Storage\StorageInterface) {
            throw new \Exception('Storage must be instance of Spork\Mvc\Listener\Limit\Storage\StorageInterface');
        }
        
        $this->storage = $storage;
        return $this;
    }
    
    /**
     * Get storage plugin manager
     * 
     * @return \Zend\ServiceManager\AbstractPluginManager
     */
    public function getStoragePlugins()
    {
        if (null === $this->storagePlugins) {
            $this->setStoragePlugins(new Storage\PluginManager());
        }
        return $this->storagePlugins;
    }
    
    /**
     * Set storage plugin manager
     * 
     * @param AbstractPluginManager $plugins
     * @return \Spork\Mvc\Listener\Limit\LimitStrategy
     */
    public function setStoragePlugins(AbstractPluginManager $plugins)
    {
        $this->storagePlugins = $plugins;
        return $this;
    }

    /**
     * Increment the count for a limit
     * 
     * @param string $limitName
     */
    public function increment($limitName)
    {
        $this->storage->increment($this->remoteAddress->getIpAddress(), $limitName);
    }

    /**
     * Configure instance and inject it into controller plugin manager
     * 
     * @param MvcEvent $event
     */
    public function initialize(MvcEvent $event)
    {
        $appConfig = $event->getApplication()->getServiceManager()->get('config');
        if (array_key_exists(self::CONFIG_KEY, $appConfig)) {
            $this->configure($appConfig[self::CONFIG_KEY]);
        }
        
        $event->getApplication()
            ->getServiceManager()
            ->get('controllerPluginManager')
            ->setService($this->pluginName, $this);
    }

    /**
     * Test limits and take actions if they have been exceeded
     * 
     * @param MvcEvent $event
     */
    public function check(MvcEvent $event)
    {
        foreach ($this->limits as $limit) {
            if ($this->storage->check($this->remoteAddress->getIpAddress(), $limit)) {
                foreach ($limit->getActions() as $action) {
                    $action($event);
                }
            }
        }
    }
    
    /**
     * Configure instance
     * 
     * @param array $options
     */
    protected function configure(array $options)
    {
        if (isset($options['actionPluginManager'])) {
            $config = new Config($options['actionPluginManager']);
            $config->configureServiceManager($this->getActionPlugins());
            unset($options['actionPluginManager']);
        }
        
        if (isset($options['storagePluginManager'])) {
            $config = new Config($options['storagePluginManager']);
            $config->configureServiceManager($this->getStoragePlugins());
            unset($options['storagePluginManager']);
        }
        
        foreach ($options as $key => $value) {
            switch ($key) {
                case 'pluginName':
                    $this->setPluginName($name);
                    break;
                case 'limits':
                    $this->setLimits($value);
                    break;
                case 'storage':
                    $this->setStorage($value);
                    break;
                case 'useProxy':
                    $this->remoteAddress->setUseProxy($value);
                    break;
                case 'trustedProxies':
                    $this->remoteAddress->setTrustedProxies($value);
                    break;
            }
        }
    }
}