<?php
/**
 * 
 * Spork Zend Framework 2 Library
 * 
 * @author Chris Schreiber <chris@sporkcode.com>
 */
namespace Spork\View\Helper;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Session\Container;

/**
 * Provides interface to configure Dojo framework and uses HeadLink and 
 * HeadScript view helpers to add framework to application.
 * 
 * Example Configuration
 * array(
 *     'dojo' => array(
 *         'async' => BOOLEAN,
 *         'debug' => BOOLEAN,
 *         'enabled' => BOOLEAN,
 *         'parseOnLoad' => BOOLEAN,
 *         'packages' => ARRAY('PACKAGE NAME', ...),
 *         'src' => 'URL',
 *     )
 * )
 */
class Dojo extends \Zend\View\Helper\AbstractHelper 
        implements \Zend\ServiceManager\FactoryInterface
{
    /**
     * Load asynchronously
     * 
     * @var boolean
     */
    protected $async;
    
    /**
     * Use debugging configuration
     * 
     * @var boolean
     */
    protected $debug = false;
    
    /**
     * Load Dojo flag
     * 
     * @var boolean
     */
    protected $enabled = false;
    
    /**
     * Initialized flag
     * 
     * @var boolean
     */
    protected $initialized = false;
    
    /**
     * List of modules to load
     * 
     * @var array
     */
    protected $modules = array();

    /**
     * List of options
     * 
     * @var array
     */
    protected $options = array();
    
    /**
     * List of packages to load
     * 
     * @var array
     */
    protected $packages = array();
    
    /**
     * Parse on load flag
     * 
     * @var boolean
     */
    protected $parseOnLoad;
    
    /**
     * Dojo source path
     * 
     * @var string
     */
    protected $src = '/dojo/dojo/dojo.js';
    
    /**
     * Initialize and configure instance
     * 
     * @param array $options
     * @return \Spork\View\Helper\Dojo
     */
    public function __invoke(array $options = null)
    {
        if (null !== $options) {
            $this->config($options);
        }
        
        return $this;
    }
    
    /**
     * Create and configure service
     * 
     * @see \Zend\ServiceManager\FactoryInterface::createService()
     * @param ServiceLocatorInterface $viewHelperManager
     * @return \Spork\View\Helper\Dojo
     */
    public function createService(ServiceLocatorInterface $viewHelperManager)
    {
        $serviceLocator = $viewHelperManager->getServiceLocator();
        $config = $serviceLocator->has('config') ? $serviceLocator->get('config') : array();
        if (isset($config['dojo'])) {
            $this->config($config['dojo']);
        }
        
        $application = $serviceLocator->get('application');
        $debug = $application->getRequest()->getQuery('debug'); 
        if ($debug !== null) {
            $this->setDebug($debug == 'true');
        }
        
        return $this;
    }
    
    /**
     * Get asynchronous flag
     * 
     * @return boolean
     */
    public function isAsync()
    {
        return (bool) $this->async;
    }

    /**
     * Set asynchronous flag
     * 
     * @param string $flag
     */
    public function setAsync($flag = true)
    {
        $this->async = (bool) $flag;
    }

    /**
     * Get is debug flag
     *
     * @return boolean
     */
    public function isDebug()
    {
        return $this->debug;
    }

    /**
     * Set debug flag
     * 
     * @param string $flag
     */
    public function setDebug($flag = true)
    {
        $this->debug = $this->getSession()->debug = (bool) $flag;
        if (true == $this->debug) {
            if (isset($this->options['debugOptions'])
                && is_array($this->options['debugOptions'])) {
                    $this->initializeOptions($this->options['debugOptions']);
                }
        } else {
            $this->initializeOptions($this->options);
        }
    }
    
    /**
     * Get enabled flag
     * 
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set enable flag
     * 
     * @param string $flag
     */
    public function setEnabled($flag = true)
    {
        $this->enabled = (bool) $flag;
    }
    
    /**
     * Add module
     *
     * @param string $module
     */
    public function addModule($module)
    {
        $this->modules[] = $module;
    }
    
    public function setModules(array $modules)
    {
        $this->modules = $modules;
    }

    /**
     * Remove module
     * 
     * @param unknown $module
     */
    public function removeModule($module)
    {
        unset($this->modules[array_search($module, $this->modules)]);
    }
    
    /**
     * Add package
     *
     * @param string $name
     * @param string $location
     */
    public function addPackage($name, $location)
    {
        $this->packages[$name] = array('name' => $name, 'location' => $location);
    }

    /**
     * Set package
     * 
     * @param array $packages
     */
    public function setPackages(array $packages)
    {
        foreach ($packages as $name => $location) {
            if (is_array($location)) {
                if (array_key_exists('name', $location)
                    && array_key_exists('location', $location)) {
                        $name       = $location['name'];
                        $location   = $location['location'];
                    } else {
                        list($name, $location) = $location;
                    }
            }
            $this->addPackage($name, $location);
        }
    }
    
    /**
     * Remove package
     * 
     * @param unknown $name
     */
    public function removePackage($name)
    {
        unset($this->packages[$name]);
    }
    
    /**
     * Get parse on load flag
     * 
     * @return boolean
     */
    public function getParseOnLoad()
    {
        return $this->parseOnLoad;
    }

    /**
     * Set parse on load flag
     * 
     * @param string $flag
     */
    public function setParseOnLoad($flag = true)
    {
        $this->parseOnLoad = (bool) $flag;
    }
    
    /**
     * Get Dojo source path
     * 
     * @return string
     */
    public function getSrc()
    {
        return $this->src;
    }

    /**
     * Set Dojo source path
     * 
     * @param unknown $src
     */
    public function setSrc($src)
    {
        $this->setEnabled(true);
        $this->src = $src;
    }
    
    /**
     * Configure object
     *
     * @param array $options
     */
    public function config(array $options)
    {
        $this->options = $options;
    
        $this->initializeOptions($options);
    
        if ($this->getSession()->debug == true) {
            $this->setDebug(true);
        }
    }
    
    /**
     * Add Dojo configuration to HeadScript. Should only be called once.
     */
    public function initialize()
    {
        if (true == $this->initialized) {
            return;
        }

        $this->initialized = true;
        
        if ($this->isEnabled()) {
            $headScript = $this->getView()->plugin('headScript');
            
            $headScript->prependFile($this->src);
            
            $options = array();
            foreach (array('async', 'parseOnLoad') as $property) {
                if (null !== $this->$property) {
                    $options[$property] = $this->$property;
                }
            }
            if (!empty($this->packages)) {
                $options['packages'] = array_values($this->packages);
            }
            if (!empty($options)) {
                $headScript->prependScript("dojoConfig=" . json_encode($options) . ";");
            }
            if (!empty($this->modules)) {
                $require = 'require(["' . implode('", "', $this->modules) . '"]);';
                $headScript->appendScript($require);
            }
        }
    }
    
    /**
     * Get session
     * 
     * @return \Zend\Session\Container
     */
    protected function getSession()
    {
        return new Container(__CLASS__);
    }
    
    /**
     * Initialize options
     * 
     * @param array $options
     */
    protected function initializeOptions(array $options)
    {
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (method_exists($this, $method)) {
                call_user_func(array($this, $method), $value);
            }
        }
    }
}