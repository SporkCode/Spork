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
     * Copy source files
     */
    protected function getCommandArguments($source, $destination = null)
    {
        $this->compiled = true;
        
        return $source;
        
        if ($source == $destination) {
            $commands = '';
            $search = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source));
            foreach ($search as $file) {
                if ($file->isFile() && $file->getExtension() == 'src') {
                    $destination = $file->getPath() . DIRECTORY_SEPARATOR 
                        . $file->getBasename('.src') . '.css';
                    $commands .= " {$file->getPathname()} $destination; ";
                }
            }
            return $commands;
        }
        if (is_dir($source)) {
            $source .= DIRECTORY_SEPARATOR . '*';
        }
        return " -r $source $destination";
    }
}

/**
 *
 */
class UpdateListenerTest extends TestCase
{
    protected $temp;
    
    public function testUpdate()
    {
        $source = $this->creatTempDirectory('source');
        touch($source . DIRECTORY_SEPARATOR . 'file1');
        mkdir($source . DIRECTORY_SEPARATOR . 'folder1');
        mkdir($source . DIRECTORY_SEPARATOR . 'folder2');
        touch($source . DIRECTORY_SEPARATOR . 'folder2' . DIRECTORY_SEPARATOR . 'file2');
        
        $compiler = new TestCompiler();
        $listener = new UpdateListener();
        $listener->setCompiler($compiler);
        $listener->setBuilds(array($source));
        $event = $this->getEvent();
        
        // do nothing on folder without source files
        $listener->updateCSS($event);
        $this->assertFalse($compiler->compiled);
        
        // update on new source file
        $compiler->compiled = false;
        touch($source . DIRECTORY_SEPARATOR . 'code1.src');
        touch($source . DIRECTORY_SEPARATOR . 'folder2' . DIRECTORY_SEPARATOR . 'code2.src');
        $listener->updateCSS($event);
        $this->assertTrue($compiler->compiled);
        
        // update on up to date
        $compiler->compiled = false;
        touch($source . DIRECTORY_SEPARATOR . 'code1.css');
        touch($source . DIRECTORY_SEPARATOR . 'folder2' . DIRECTORY_SEPARATOR . 'code2.css');
        $listener->updateCSS($event);
        $this->assertFalse($compiler->compiled);
        
        // update on changed
        $compiler->compiled = false;
        touch($source . DIRECTORY_SEPARATOR . 'folder2' . DIRECTORY_SEPARATOR . 'code2.css', time() - 10);
        $listener->updateCSS($event);
        $this->assertTrue($compiler->compiled);
        
        // update on separate destination
        $compiler->compiled = false;
        $destination = $this->creatTempDirectory('destination');
        $listener->setBuilds(array(array('source' => $source, 'destination' => $destination)));
        $listener->updateCSS($event);
        $this->assertTrue($compiler->compiled);
        
        $compiler->compiled = false;
        touch($destination . DIRECTORY_SEPARATOR . 'code1.css');
        mkdir($destination . DIRECTORY_SEPARATOR . 'folder2');
        touch($destination . DIRECTORY_SEPARATOR . 'folder2' . DIRECTORY_SEPARATOR . 'code2.css');
        $listener->updateCSS($event);
        $this->assertFalse($compiler->compiled);
    }
    
    protected function setUp()
    {
        $this->temp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'unittest' . rand();
        mkdir($this->temp);
    }
    
    protected function tearDown()
    {
        foreach (new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(
                    $this->temp, 
                    \FilesystemIterator::CURRENT_AS_FILEINFO | \FilesystemIterator::SKIP_DOTS), 
                \RecursiveIteratorIterator::CHILD_FIRST) as $file) {
            if (is_dir($file)) {
                rmdir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($this->temp);
    }
    
    protected function creatTempDirectory($name)
    {
        $directory = $this->temp . DIRECTORY_SEPARATOR . $name;
        mkdir($directory);
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