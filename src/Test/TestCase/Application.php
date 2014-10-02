<?php
/**
 * 
 * Spork Zend Framework 2 Library
 * 
 * @author Chris Schreiber <chris@sporkcode.com>
 */
namespace Spork\Test\TestCase;

/**
 * Boostraps the Application
 * 
 * This is mostly usefull to tests on plugins that rely on initialization that happens durring bootstrap
 * such as view helpers.
 *
 * @deprecated there are better ways to do this
 */
class Application extends Service
{
    /**
     * Bootstrap application
     * 
     * @see \Spork\Test\TestCase\Service::setUp()
     */
    protected function setUp()
    {
        parent::setUp();
        
        $this->services->get('application')->bootstrap();
    }
}