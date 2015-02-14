<?php
/**
 * 
 * Spork Zend Framework 2 Library
 * 
 * @author Chris Schreiber <chris@sporkcode.com>
 */

namespace Spork\CSS;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Cache\StorageFactory;
use Zend\Cache\Storage\Adapter\AbstractAdapter as AbstractCacheAdapter;

/**
 * Base class for CSS Preprocessor. Provides basic configuration, Service
 * Manager Factory Interface and compile method.
 * 
 * Child classes must implement getCommandArguments method and should override
 * compiler, extensions and configurationKey methods.
 */
abstract class AbstractCompiler implements FactoryInterface
{
    /**
     * Option arguments to be passed to compiler
     * 
     * @var array
     */
    protected $arguments = array();
    
    /**
     * @var \Zend\Cache\Storage\Adapter\AbstractAdapter
     */
    protected $cache;
    
    /**
     * Full path to compiler executable
     * 
     * @var string
     */
    protected $compiler;

    /**
     * @var boolean
     */
    protected $compress = false;
    
    /**
     * Key name for options in configuration file.
     * Add definition in child class to enable service configuration
     * 
     * @var string
     */
    protected $configurationKey;
    
    /**
     * List of file extension of source files
     * 
     * @var array
     */
    protected $extensions = array();
    
    /**
     * List of inlude paths for compiler to use
     * 
     * @var array
     */
    protected $includes = array();

    /**
     * Return command line arguments to compile source file or path. If 
     * destination is not specified command should print code to standard out.
     *
     * @param string $source Source file or path
     * @param string $destination Destination file or path
     * @param array $includes List of include paths
     * @return string
     */
    abstract protected function getCommandArguments($source, $destination, array $includes);
    
    /**
     * Compile to file. If destination is not specified return CSS.
     * 
     * @param string $source
     * @param string|null $destination 
     * @param array|string|null $include Include path(s) to use
     * @throws \Exception on compilation error
     * @return string Compiled CSS
     */
    public function compile($source, $destination = null, $include = null)
    {
        $include = array_merge($this->includes, (array) $include);
        
        $useCache = null == $destination && null !== $this->cache;
         
        if ($useCache) {
            if ($this->cache->hasItem($source)) {
                return $this->cache->getItem($source);
            }
        }

        $arguments = implode(' ', array_map('escapeshellarg', $this->arguments));
        $arguments .= ' ' . $this->getCommandArguments($source, $destination, $include);
        
        $command = "{$this->compiler} $arguments" . ' 2>&1'; 

        $result = exec($command, $output, $exitCode);
        if ($exitCode != 0) {
            throw new \Exception(sprintf('Error compiling CSS "%s": %s', 
                $source, 
                implode(PHP_EOL, $output)));
        }
        
        $css = implode(PHP_EOL, $output);
        
        if ($useCache) {
            $this->cache->setItem($source, $css);
        }
        
        return $css;
    }

    /**
     * Get compiler arguments
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }
    
    /**
     * Set compiler arguments
     * @param array $arguments
     */
    public function setArguments($arguments)
    {
        $this->arguments = (array) $arguments;
    }
    
    /**
     * Get cache
     * 
     * @return \Zend\Cache\Storage\Adapter\AbstractAdapter
     */
    public function getCache()
    {
        return $this->cache;
    }
    
    /**
     * Set cache for CSS Preprocessor
     * 
     * Cache parameter can be an instance of \Zend\Cache\Storage\Adapter\AbstractAdapter,
     * the name of a cache adapter class or an array of options to be passed
     * to AbstractAdapter::factory()
     * 
     * @param string|array|\Zend\Cache\Storage\Adapter\AbstractAdapter $cache
     * @throws \Exception on invalid cache
     */
    public function setCache($cache)
    {
        if (is_string($cache) && class_exists($cache)) {
            $cache = new $cache();
        }
        
        if (is_array($cache)) {
            $cache = StorageFactory::factory($cache);
        }
        
        if (!$cache instanceof AbstractCacheAdapter) {
            throw new \Exception('Invalid Cache');
        }
        
        $this->cache = $cache;
    }
    
    /**
     * Get compiler path
     * 
     * @return string
     */
    public function getCompiler()
    {
        return $this->compiler;
    }
    
    /**
     * Set compiler path
     * 
     * @param string $compiler Compiler Path
     */
    public function setCompiler($compiler)
    {
        $this->compiler = $compiler;
    }

    /**
     * Get compress flag
     * 
     * @return boolean
     */
    public function getCompress()
    {
        return $this->compress;
    }
    
    /**
     * Set compress flag
     * 
     * @param boolean $flag
     */
    public function setCompress($flag)
    {
        $this->compress = $flag == true;
    }
    
    /**
     * Get source file extension
     * 
     * @return string
     */
    public function getExtensions()
    {
        return $this->extensions;
    }
    
    /**
     * Set source file extensions
     * 
     * @param array $extensions
     */
    public function setExtensions(array $extensions)
    {
        $this->extensions = $extensions;
    }
    
    public function addInclude($include)
    {
        $this->includes[] = $include;
    }
    
    /**
     * Get compiler include path
     * 
     * @return string|boolean|NULL
     */
    public function getIncludes()
    {
        return $this->includes;
    }
    
    /**
     * Set compiler include path
     * 
     * If set to true the source path is used.
     * 
     * @param string|boolean|NULL $path
     */
    public function setIncludes(array $includes)
    {
        $this->includes = array();
        foreach ($includes as $include) {
            $this->addInclude($include);
        }
    }
    
    /**
     * Create Service
     * 
     * @see \Zend\ServiceManager\FactoryInterface::createService()
     * @param ServiceLocatorInterface $services
     * @return \Spork\Style\AbstractStyle
     */
    public function createService(ServiceLocatorInterface $services)
    {
        if (null !== $this->configurationKey) {
            $this->configure($this->getOptions($services, $this->configurationKey));
        }
        
        return $this;
    }
    
    /**
     * configure
     * @param array $options
     */
    protected function configure(array $options)
    {
        foreach ($options as $key => $value) {
            switch (strtolower($key)) {
                case 'arguments':
                    $this->setArguments($value);
                    break;
                case 'cache':
                    $this->setCache($value);
                    break;
                case 'compiler':
                    $this->setCompiler($value);
                    break;
                case 'compress':
                    $this->setCompress($value);
                    break;
                case 'extension':
                    $this->setExtension($value);
                    break;
                case 'include':
                    $this->setInclude($value);
                    break;
            }
        }
    }
    
    /**
     * Get options from application configuration
     * 
     * @param ServiceLocatorInterface $services
     * @param string $key
     * @return array
     */
    protected function getOptions(ServiceLocatorInterface $services, $key)
    {
        $appConfig = $services->get('config');
        $options = array_key_exists($key, $appConfig) ? $appConfig[$key] : array();
        if (array_key_exists('cache', $options) 
                && is_string($options['cache']) 
                && $services->has($options['cache'])) {
            $options['cache'] = $services->get($options['cache']);
        }
        return $options;
    }
}