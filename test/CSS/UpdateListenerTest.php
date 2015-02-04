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
    
    /**
     * Test scanning build where source and destination are same directory
     */
    public function testDirectory()
    {
        $source = $this->creatTempDirectory('source');
        
        $listener = $this->listener;
        $listener->setBuilds(array($source));
        $event = $this->getEvent();
        
        // do nothing on folder without source files
        $this->assertFalse($listener->updateCSS($event));
        
        // update on new source file
        touch($source . DIRECTORY_SEPARATOR . 'code1.src');
        $this->assertTrue($listener->updateCSS($event));
        
        // target up to date
        touch($source . DIRECTORY_SEPARATOR . 'code1.css');
        $this->assertFalse($listener->updateCSS($event));
        
        // update new source in sub folder
        touch($source . DIRECTORY_SEPARATOR . 'folder2' . DIRECTORY_SEPARATOR . 'code2.src');
        $this->assertTrue($listener->updateCSS($event));
        
        // target up to date
        touch($source . DIRECTORY_SEPARATOR . 'folder2' . DIRECTORY_SEPARATOR . 'code2.css');
        $this->assertFalse($listener->updateCSS($event));
        
        // update on changed
        touch($source . DIRECTORY_SEPARATOR . 'folder2' . DIRECTORY_SEPARATOR . 'code2.css', time() - 10);
        $this->assertTrue($listener->updateCSS($event));
    }
    
    /**
     * Test scanning build where source and destination are different directories 
     */
    public function testDestinationDirectory()
    {
        $source = $this->creatTempDirectory('source');
        $destination = $this->creatTempDirectory('destination');
        $event = $this->getEvent();
        
        $listener = $this->listener;
        $listener->setBuilds(array(array('source' => $source, 'destination' => $destination)));
        
        // Do nothing on no source files
        $this->assertFalse($listener->updateCSS($event));
        
        // New source
        touch($source . DIRECTORY_SEPARATOR . 'code1.src');
        $this->assertTrue($listener->updateCSS($event));
        
        // Target up to date
        touch($destination . DIRECTORY_SEPARATOR . 'code1.css');
        $this->assertFalse($listener->updateCSS($event));
        
        // New source in sub folder
        touch($source . DIRECTORY_SEPARATOR . 'folder1' . DIRECTORY_SEPARATOR . 'code2.src');
        $this->assertTrue($listener->updateCSS($event));
        
        // Target up to date
        touch($destination . DIRECTORY_SEPARATOR . 'folder1' . DIRECTORY_SEPARATOR . 'code2.css');
        $this->assertFalse($listener->updateCSS($event));
        
        // Target out of date
        touch($destination . DIRECTORY_SEPARATOR . 'code1.css', time() - 2);
        $this->assertTrue($listener->updateCSS($event));
    }
    
    /**
     * Test scanning where source is single file 
     */
    public function testUpdateFile()
    {
        $sourceDir = $this->creatTempDirectory('source');
        $source = $sourceDir . DIRECTORY_SEPARATOR . 'code.src';
        
        $listener = $this->listener;
        $listener->addBuild($source);
        
        $event = $this->getEvent();
        
        // Target does not exist
        touch($source);
        $this->assertTrue($listener->updateCSS($event));
        
        // Target update to date
        touch($sourceDir . DIRECTORY_SEPARATOR . 'code.css');
        $this->assertFalse($listener->updateCSS($event));
        
        // Target out of date
        touch($sourceDir . DIRECTORY_SEPARATOR . 'code.css', time() - 2);
        $this->assertTrue($listener->updateCSS($event));
    }
    
    /**
     * Test scanning with include files 
     */
    public function testInclude()
    {
        $source = $this->creatTempDirectory('source');
        touch($source . DIRECTORY_SEPARATOR . 'code.src', time() - 1);
        touch($source . DIRECTORY_SEPARATOR . 'code.css', time() - 1);
        $include = $this->creatTempDirectory('include');
        
        $listener = $this->listener;
        $listener->addBuild(array(
            'source' => $source,
            'includes' => array($include),
        ));
        
        $event = $this->getEvent();
        
        // Source empty
        $this->assertFalse($listener->updateCSS($event));
        
        // Include updated
        touch($include . DIRECTORY_SEPARATOR . 'include.src');
        $this->assertTrue($listener->updateCSS($event));
        
        // target up to date
        touch($source . DIRECTORY_SEPARATOR . 'code.css');
        $this->assertFalse($listener->updateCSS($event));
    }
    
    protected function setUp()
    {
        // Initialize temporary directory
        $this->tempdir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'unittest' . rand();
        mkdir($this->tempdir);
        
        // Initialize listener
        $this->listener = new TestUpdateListener();
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
    
    protected function creatTempDirectory($name)
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