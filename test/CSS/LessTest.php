<?php
/**
 * 
 * Spork Zend Framework 2 Library
 * 
 * @author Chris Schreiber <chris@sporkcode.com>
 */

namespace SporkTest\CSS;

use Spork\Test\TestCase\TestCase;
use Spork\CSS\Less;

/**
 *
 */
class LessTest extends TestCase
{
    /**
     * @var \Spork\CSS\Less
     */
    protected $less;
    
    protected $configurationKey = 'css-less';
    
    protected function setUp()
    {
        $this->less = new Less();
    }
    
    public function testCompile()
    {
        // to standard output
        $less = $this->less;
        $css = $less->compile(__DIR__ . '/lessCode.less');
        $this->assertEquals(file_get_contents(__DIR__ . '/lessOutput.css'), $css);

        // to file
        $destination = tempnam(sys_get_temp_dir(),'/unittest');
        $less->compile(__DIR__ . '/lessCode.less', $destination);
        $this->assertEquals(file_get_contents(__DIR__ . '/lessOutput.css'), trim(file_get_contents($destination)));
        unlink($destination);
        
        // compressed to standard output
        $less->setCompress(true);
        $css = $less->compile(__DIR__ . '/lessCode.less');
        $this->assertEquals(file_get_contents(__DIR__ . '/lessOutputCompress.css'), $css);
    }
}