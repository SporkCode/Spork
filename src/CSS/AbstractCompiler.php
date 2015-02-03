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
     * Inlude path for compiler to use. It true compiler should use source path
     * as include path. If false or null no include path is used;
     * 
     * @var string|boolean|null
     */
    protected $include;

    /**
     * Return command line arguments to compile source file or path. If 
     * destination is not specified command should print code to standard out.
     *
     * @param string $source Source file or path
     * @param string $destination Destination file or path
     * @return string
     */
    abstract protected function getCommandArguments($source, $destination = null);
    
    /**
     * Compile to file. If destination is not specified return CSS.
     * 
     * @param string $source
     * @param string|null $destination 
     * @throws \Exception on compilation error
     * @return string Compiled CSS
     */
    public function compile($source, $destination = null)
    {
        $useCache = null == $destination && null !== $this->cache;
         
        if ($useCache) {
            if ($this->cache->hasItem($source)) {
                return $this->cache->getItem($source);
            }
        }

        $arguments = implode(' ', array_map('escapeshellarg', $this->arguments));
        $arguments .= ' ' . $this->getCommandArguments($source, $destination);
        
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
    
    /**
     * Get compiler include path
     * 
     * @return string|boolean|NULL
     */
    public function getInclude()
    {
        return $this->include;
    }
    
    /**
     * Set compiler include path
     * 
     * If set to true the source path is used.
     * 
     * @param string|boolean|NULL $path
     */
    public function setInclude($path)
    {
        $this->include = $path;
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
     * Get include path based on include path property and source paramater
     * 
     * @param string|null $source
     */
    protected function findInclude($source)
    {
        if (true === $this->include) {
            return is_dir($source) ? $source : dirname($source);
        }
        
        if (is_string($this->include)) {
            return $this->include;
        }
        
        return null;
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