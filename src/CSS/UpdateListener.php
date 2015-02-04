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
    
    //protected $include;
    
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
                $build['destination'] = is_file($build['source']) ? 
                        dirname($build['source']) : $build['source'];
            } elseif (!file_exists($build['destination'])) {
                throw new \Exception('CSS Update Listener: Destination not found');
            }
            
            if (!array_key_exists('includes', $build)) {
                $build['includes'] = null;
            }
            
            if ($this->isOutOfDate($build['source'], $build['destination'], $build['includes'])) {
                $compressReset = null;
                if (isset($build['compress'])) {
                    $compressReset = $this->compiler->getCompress();
                    $this->compiler->setCompress($build['compress']);
                }
                $this->compiler->compile($build['source'], $build['destination'], $build['includes']);
                if (null !== $compressReset) {
                    $this->compiler->setCompress($compressReset);
                }
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
    
//     public function getInclude()
//     {
//         return $this->include;
//     }
    
//     public function setInclude($include)
//     {
//         $this->include = $include;
//     }

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
//                 case 'include':
//                     $this->setInclude($include);
//                     break;
            }
        }
    }
    
    /**
     * Get relative key for a file
     * 
     * @param string $basepath
     * @param \SplFileInfo $file
     */
    protected function getFileKey($basePath, \SplFileInfo $file)
    {
        $baseLength = strlen(rtrim($basePath, DIRECTORY_SEPARATOR));
        
        return substr($file->getPath(), $baseLength) . DIRECTORY_SEPARATOR 
                . $file->getBasename('.' . $file->getExtension());
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
    protected function isOutOfDate($source, $destination, array $includes = null)
    {
        $includes = (array) $includes;
        $extensions = $this->compiler->getExtensions();
        
        $sourceFiles = $this->scanDirectory($source, $extensions);
        $destinationFiles = $this->scanDirectory($destination, array('css'));
        
        foreach ($sourceFiles as $key => $file) {
            if (array_key_exists($key, $destinationFiles)) {
                if ($file->getMTime() > $destinationFiles[$key]->getMTime()) {
                    return true;
                }
            } else {
                return true;
            }
        }
        
        $oldest = time();
        foreach ($destinationFiles as $file) {
            $modified = $file->getMTime(); 
            if ($modified < $oldest) {
                $oldest = $modified;
            }
        }
        
        foreach ($includes as $include) {
            $includeFiles = $this->scanDirectory($include, $extensions);
            foreach ($includeFiles as $file) {
                if ($file->getMTime() > $oldest) {
                    return true;
                }
            }
        }
        
        return false;
        
        /*
        
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
            if (!$destination->isFile()) {
                return true;
            }
        }
        
        return $source->getMTime() > $destination->getMTime();
        */
    }
    
    /**
     * Scan a directory and return a list of files with matching extenstions
     * 
     * @param string $path Directory path to scan
     * @param string|array $extensions Match files with these extensions
     */
    protected function scanDirectory($path, $extensions)
    {
        if (is_file($path)) {
            $file = new \SplFileInfo($path);
            return array($this->getFileKey($file->getPath(), $file) => $file);
        }
        
        $files = array();
        $extensions = (array) $extensions;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $path, 
                \FileSystemIterator::SKIP_DOTS 
                    | \FileSystemIterator::CURRENT_AS_FILEINFO)    
        );
        foreach ($iterator as $file) {
            if (in_array($file->getExtension(), $extensions)) {
                $files[$this->getFileKey($path, $file)] = $file;
            }
        }
        return $files;
    }
}