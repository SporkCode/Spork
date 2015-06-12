<?php
/**
 * 
 * Spork Zend Framework 2 Library
 * 
 * @author Chris Schreiber <chris@sporkcode.com>
 */

namespace SporkTest\CSS;

use Spork\Test\TestCase\TestCase;
use Spork\CSS\AbstractCompiler;
use Spork\CSS\UpdateListener;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Application;
use Zend\ServiceManager\ServiceManager;
use Zend\EventManager\EventManager;
use Spork\CSS\Spork\CSS;

class TestCompiler extends AbstractCompiler
{
    public $compiled = false;

    protected $compiler = 'echo';
    
    protected $extensions = array('src');
    
    /**
     * Dummy function sets compiled flag to true
     */
    protected function getCommandArguments($source, $destination, array $includes)
    {
        $this->compiled = true;
        
        return "$source $destination";
    }
}

class TestUpdateListener extends UpdateListener
{
    public function __construct(array $options = null)
    {
        parent::__construct(array('compiler' => new TestCompiler()));
    }

    public function updateCSS(MvcEvent $event)
    {
        $this->compiler->compiled = false;
        parent::updateCSS($event);
        return $this->compiler->compiled;
    }
}

/**
 *
 */
class UpdateListenerTest extends TestCase
{
    /**
     * @var \Spork\CSS\UpdateListener
     */
    protected $listener;
    
    protected $tempdir;
    
    public function testEmptyDirectory()
    {
        $source = $this->createTempDirectory('source');
        
        $this->assertNotCompile($source);
    }
    
    public function testNewSource()
    {
        $source = $this->createTempDirectory('source');
        
        touch($source . DIRECTORY_SEPARATOR . 'code1.src');

        $this->assertCompile($source);
    }
    
    public function testUpToDate()
    {
        $source = $this->createTempDirectory('source');
        
        touch($source . DIRECTORY_SEPARATOR . 'code1.src');
        touch($source . DIRECTORY_SEPARATOR . 'code1.css');
        
        $this->assertNotCompile($source);
    }
    
    public function testUpdated()
    {
        $source = $this->createTempDirectory('source');
        
        touch($source . DIRECTORY_SEPARATOR . 'code1.src');
        touch($source . DIRECTORY_SEPARATOR . 'code1.css', time() - 60);
        
        $this->assertCompile($source);
    }
    
    public function testSubFolder()
    {
        $source = $this->createTempDirectory('source');
        
        touch($source . DIRECTORY_SEPARATOR . 'folder2' . DIRECTORY_SEPARATOR . 'code2.src');
        
        $this->assertCompile($source);
    }
    
    public function testSubFolderUpToDate()
    {
        $source = $this->createTempDirectory('source');
        
        touch($source . DIRECTORY_SEPARATOR . 'folder2' . DIRECTORY_SEPARATOR . 'code2.src');
        touch($source . DIRECTORY_SEPARATOR . 'folder2' . DIRECTORY_SEPARATOR . 'code2.css');
        
        $this->assertNotCompile($source);
    }
    
    /**
     * Test scanning build where source and destination are different directories 
     */
    public function testSeparateDestination()
    {
        $source = $this->createTempDirectory('source');
        $destination = $this->createTempDirectory('destination');
        
        touch($source . DIRECTORY_SEPARATOR . 'code1.src');
        
        $this->assertCompile($source, $destination);
    }
    
    /**
     * Test separate destination folder with up to date files 
     */
    public function testSeparateDestinationUpToDate()
    {
        $source = $this->createTempDirectory('source');
        $destination = $this->createTempDirectory('destination');
        
        touch($source . DIRECTORY_SEPARATOR . 'code1.src');
        touch($destination . DIRECTORY_SEPARATOR . 'code1.css');
        
        $this->assertNotCompile($source, $destination);
    }
    
    public function testSourceFile()
    {
        $sourceDir = $this->createTempDirectory('source');
        $source = $sourceDir . DIRECTORY_SEPARATOR . 'code.src';
        
        touch($source);
        $this->assertCompile($source);
    }
    
    public function testSourceFileUpToDate()
    {
        $sourceDir = $this->createTempDirectory('source');
        $source = $sourceDir . DIRECTORY_SEPARATOR . 'code.src';
        
        touch($source);
        touch($sourceDir . DIRECTORY_SEPARATOR . 'code.css');
        
        $this->assertNotCompile($source);
    }
    
    /**
     * Test scanning with include files 
     */
    public function testInclude()
    {
        $source = $this->createTempDirectory('source');
        $include = $this->createTempDirectory('include');
        
        touch($include . DIRECTORY_SEPARATOR . 'include.src', time() - 60);
        touch($source . DIRECTORY_SEPARATOR . 'code.src');
        touch($source . DIRECTORY_SEPARATOR . 'code.css');
        
        $this->assertNotCompile($source, null, $include);
    }
    
    public function testIncludeUpToDate()
    {
        $source = $this->createTempDirectory('source');
        $include = $this->createTempDirectory('include');
        
        touch($include . DIRECTORY_SEPARATOR . 'include.src');
        touch($source . DIRECTORY_SEPARATOR . 'code.src');
        touch($source . DIRECTORY_SEPARATOR . 'code.css');
        
        $this->assertNotCompile($source, null, $include);
    }
    
    protected function setUp()
    {
        // Initialize temporary directory
        $this->tempdir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'unittest' . rand();
        mkdir($this->tempdir);
        
        // Initialize listener
        //$this->listener = new TestUpdateListener();
        $compiler = $this->getMockBuilder('Spork\CSS\Less')->getMock();
        $compiler->method('getExtensions')->willReturn(array('src'));
        
        $this->listener = new UpdateListener();
        $this->listener->setCompiler($compiler);
    }
    
    protected function tearDown()
    {
        foreach (new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(
                    $this->tempdir, 
                    \FilesystemIterator::CURRENT_AS_FILEINFO | \FilesystemIterator::SKIP_DOTS), 
                \RecursiveIteratorIterator::CHILD_FIRST) as $file) {
            if (is_dir($file)) {
                rmdir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($this->tempdir);
    }
    
    protected function assertCompile($source, $destination = null, $include = null, $compile = true)
    {
        if (null === $destination) {
            $this->listener->setBuilds(array($source));
        } else {
            $this->listener->setBuilds(array(
                array('source' => $source, 'destination' => $destination)));
        }
        $this->listener->getCompiler()->expects(
            $compile ? $this->once() : $this->never())->method('compile');
        $this->listener->updateCSS($this->getEvent());
    }
    
    protected function assertNotCompile($source, $destination = null, $include = null)
    {
        $this->assertCompile($source, $destination, $include, false);
    }
    
    protected function createTempDirectory($name)
    {
        $directory = $this->tempdir . DIRECTORY_SEPARATOR . $name;
        mkdir($directory);
        touch($directory . DIRECTORY_SEPARATOR . 'file1');
        mkdir($directory . DIRECTORY_SEPARATOR . 'folder1');
        mkdir($directory . DIRECTORY_SEPARATOR . 'folder2');
        touch($directory . DIRECTORY_SEPARATOR . 'folder2' . DIRECTORY_SEPARATOR . 'file2');
        
        return $directory;
    }
    
    protected function getEvent()
    {
        $event = new MvcEvent();
        $event->setApplication(Application::init(array(
            'modules' => array(),
            'module_listener_options' => array())));
        return $event;
    }
}