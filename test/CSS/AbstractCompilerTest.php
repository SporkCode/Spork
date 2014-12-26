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
use Zend\ServiceManager\ServiceManager;
use Zend\Cache\Storage\Adapter\Memory;

/**
 * SporkTest\CSS\TestCSS
 */
class TestCSS extends AbstractCompiler
{
    protected $compiler = 'echo';
    
    protected $configurationKey = 'css-test';
    
    protected function getCommandArguments($source, $destination = null)
    {
        return escapeshellarg(md5($source));
    }
}

/**
 *
 */
class AbstractCompilerTest extends TestCase
{
    /**
     * @var TestCSS
     */
    protected $css;
    
    protected function setUp()
    {
        parent::setUp();
        
        $this->css = new TestCSS();
    }
    
    public function testCompile()
    {
        $result = md5(__FILE__);
        
        // basic test
        $css = $this->css;
        $this->assertEquals($result, $css->compile(__FILE__));
        
        // test cache
        $cache = new Memory();
        $css->setCache($cache);
        $this->assertEquals($result, $css->compile(__FILE__));
        $this->assertEquals($result, $cache->getItem(__FILE__));
        $cache->setItem(__FILE__, 'foo');
        $this->assertEquals('foo', $css->compile(__FILE__));
    }
    
    /**
     * Test getting / setting basic properties
     */
    public function testSetProperties()
    {
        $this->css->setArguments($arguments = array(md5(time() . rand())));
        $this->assertEquals($arguments, $this->css->getArguments());
        
        $this->css->setCompiler($compiler = md5(time() . rand()));
        $this->assertEquals($compiler, $this->css->getCompiler());
        
        $this->css->setCompress(true);
        $this->assertTrue($this->css->getCompress());
    }
    
    /**
     * Test cache configuration by class name
     */
    public function testSetCacheClass()
    {
        $this->css->setCache('Zend\Cache\Storage\Adapter\Memory');
        $this->assertInstanceOf('Zend\Cache\Storage\Adapter\Memory', $this->css->getCache());
    }
    
    /**
     * Test cache configuration by array
     */
    public function testSetCacheArray()
    {
        $this->css->setCache(array(
            'adapter' => array(
                'name' => 'memory', 
                'options' => array(
                    'ttl' => 60,
                )
            )
        ));
        $this->assertInstanceOf('Zend\Cache\Storage\Adapter\Memory', $this->css->getCache());
        $this->assertEquals(60, $this->css->getCache()->getOptions()->ttl);
    }
    
    /**
     * Test cache configuration by instance
     */
    public function testSetCacheInstance()
    {
        $cache = new \Zend\Cache\Storage\Adapter\Memory();
        $this->css->setCache($cache);
        $this->assertEquals($cache, $this->css->getCache());
    }
    
    public function testCreateService()
    {
        $cache = new \Zend\Cache\Storage\Adapter\Memory();
        
        $services = new ServiceManager();
        $services->setService('testCSSCache', $cache);
        $services->setService('config', array(
            'css-test' => array(
                'compiler' => 'testCompiler',
                'arguments' => array('testArgument'),
                'compress' => true,
                'cache' => 'testCSSCache',
            )
        ));
        $services->setFactory('css', 'SporkTest\CSS\TestCSS');
        
        $css = $services->get('css');
        
        $this->assertInstanceOf('SporkTest\CSS\TestCSS', $css);
        $this->assertEquals('testCompiler', $css->getCompiler());
        $this->assertEquals(array('testArgument'), $css->getArguments());
        $this->assertTrue($css->getCompress());
        $this->assertEquals($cache, $css->getCache());
    }
}