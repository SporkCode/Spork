<?php
/**
 * 
 * Spork Zend Framework 2 Library
 * 
 * @author Chris Schreiber <chris@sporkcode.com>
 */

namespace Spork\CSS;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceLocatorInterface;

use Spork\CSS\AbstractCompiler;

/**
 * Event listener to update CSS files 
 */
class UpdateListener extends AbstractListenerAggregate
{
    /**
     * @var \Spork\CSS\AbstractCompiler
     */
    protected $compiler;
    
    protected $builds = array();
    
    protected $include;
    
    public function __construct(array $options = array())
    {
        $this->configure($options);
    }
    
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, array($this, 'updateCSS'), -100);
    }
    
    public function updateCSS(MvcEvent $event)
    {
        // import options from application configuration
        $services = $event->getApplication()->getServiceManager();
        $appConfig = $services->get('config');
        if (array_key_exists('css-update', $appConfig)) {
            $updateConfig = $appConfig['css-update'];
            if (array_key_exists('compiler', $updateConfig)
                    && is_string($updateConfig['compiler'])
                    && $services->has($updateConfig['compiler'])) {
                $updateConfig['compiler'] = $services->get($updateConfig['compiler']);
            }
            $this->configure($updateConfig);
        }
        
        // check properties
        if (null === $this->compiler) {
            throw new \Exception('CSS Update Listener: Compiler not set');
        }
        
        foreach ($this->builds as $build) {
            if (!isset($build['source'])) {
                throw new \Exception('CSS Update Listener: Source not set');
            }
            if (!file_exists($build['source'])) {
                throw new \Exception('CSS Update Listener: Source not found');
            }
            if (!isset($build['destination'])) {
                $build['destination'] = $build['source'];
            } elseif (!file_exists($build['destination'])) {
                throw new \Exception('CSS Update Listener: Destination not found');
            }
            
            if ($this->isOutOfDate($build['source'], $build['destination'])) {
                $result = array();
                if (isset($build['compress'])) {
                    $result['compress'] = $this->compiler->getCompress();
                    $this->compiler->setCompress($build['compress']);
                }
                
                if (isset($build['includePath'])) {
                    $result['includePath'] = $this->compiler->getIncludePath();
                    $this->compiler->setIncludePath($build['includePath']);
                }
                
                $this->compiler->compile($build['source'], $build['destination']);
            }
        }
    }
    
    public function addBuild($build)
    {
        if (is_string($build)) {
            $build = array('source' => $build);
        }
        
        if (!is_array($build)) {
            throw new \Exception('Invalid build for CSS update');
        }
        
        $this->builds[] = $build;
    }
    
    public function getBuilds()
    {
        return $this->builds;
    }
    
    public function setBuilds(array $builds)
    {
        $this->builds = array();
        foreach ($builds as $build) {
            $this->addBuild($build);
        }
    }
    
    public function getCompiler()
    {
        return $this->compiler;
    }
    
    public function setCompiler($compiler)
    {
        if (is_string($compiler) && class_exists($compiler)) {
            $compiler = new $compiler();
        }
        
        if (!$compiler instanceof AbstractCompiler) {
            throw new \Exception(sprintf('Invalid compiler type (%s)', 
                is_object($compiler) ? get_class($compiler) : gettype($compiler)));
        }
        
        $this->compiler = $compiler;
    }
    
    public function getInclude()
    {
        return $this->include;
    }
    
    public function setInclude($include)
    {
        $this->include = $include;
    }

    /**
     * Test is destination file or folder is out of date from the source.
     * If source is a directory it recursively searches the directories.
     * Ignores non source files or folders that do not contain source files.
     *
     * @param string $source
     * @param string $destination
     * @return boolean
     */
    protected function isOutOfDate($source, $destination)
    {
        $extensions = $this->compiler->getExtensions();
        
        if (is_dir($source)) {
            $sourceIterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($source, 
                    \FilesystemIterator::KEY_AS_PATHNAME 
                    | \FilesystemIterator::CURRENT_AS_FILEINFO 
                    | \FilesystemIterator::SKIP_DOTS)
            );
            foreach ($sourceIterator as $file) {
                if (in_array($file->getExtension(), $extensions)) {
                    $cssFile = new \SplFileInfo($destination 
                        . substr($file->getPath(), strlen($source)) 
                        . DIRECTORY_SEPARATOR . $file->getBasename($file->getExtension()) . 'css');
                    if (!file_exists($cssFile) || $file->getMTime() > $cssFile->getMTime()) {
                        return true;
                    }
                }
            }
            return false;
        }
        
        $source = new \SplFileInfo($source);
        $destination = new \SplFileInfo($destination);

        if ($destination->isDir()) {
            $destination = new \SplFileInfo($destination . DIRECTORY_SEPARATOR . $source->getBasename($source->getExtension()) . 'css');
        }
        
        return $source->getMTime() > $destination->getMTime();
    }
    
    protected function configure($options)
    {
        foreach ($options as $key => $value) {
            switch ($key) {
                case 'compiler':
                    $this->setCompiler($value);
                    break;
                case 'builds':
                    $this->setBuilds($value);
                    break;
                case 'include':
                    $this->setInclude($include);
                    break;
            }
        }
    }
}