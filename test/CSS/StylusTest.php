<?php
/**
 * 
 * Spork Zend Framework 2 Library
 * 
 * @author Chris Schreiber <chris@sporkcode.com>
 */

namespace SporkTest\CSS;

use Spork\Test\TestCase\TestCase;
use Spork\CSS\Stylus;

/**
 *
 */
class StylusTest extends TestCase
{
    /**
     * @var \Spork\CSS\Stylus
     */
    protected $stylus;
    
    protected $configurationKey = 'css-stylus';
    
    protected function setUp()
    {
        $this->stylus = new Stylus();
    }
    
    public function testCompile()
    {
        // to standard output
        $stylus = $this->stylus;
        $css = $stylus->compile(__DIR__ . '/stylusCode.styl');
        $this->assertEquals(file_get_contents(__DIR__ . '/stylusOutput.css'), $css);

        // to file
        $destination = tempnam(sys_get_temp_dir(),'/unittest');
        $stylus->compile(__DIR__ . '/stylusCode.styl', $destination);
        $this->assertEquals(file_get_contents(__DIR__ . '/stylusOutput.css'), trim(file_get_contents($destination)));
        unlink($destination);
        
        // compressed to standard output
        $stylus->setCompress(true);
        $css = $stylus->compile(__DIR__ . '/stylusCode.styl');
        $this->assertEquals(file_get_contents(__DIR__ . '/stylusOutputCompress.css'), $css);
    }
}