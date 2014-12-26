<?php
/**
 * 
 * Spork Zend Framework 2 Library
 * 
 * @author Chris Schreiber <chris@sporkcode.com>
 */

namespace SporkTest\CSS;

use Spork\Test\TestCase\TestCase;
use Spork\CSS\Sass;

/**
 *
 */
class SassTest extends TestCase
{
    /**
     * @var \Spork\CSS\Sass
     */
    protected $sass;
    
    protected $configurationKey = 'css-sass';
    
    protected function setUp()
    {
        $this->sass = new Sass();
    }
    
    public function testCompile()
    {
        // to standard output
        $sass = $this->sass;
        $css = $sass->compile(__DIR__ . '/sassCode.sass');
        $this->assertEquals(file_get_contents(__DIR__ . '/sassOutput.css'), $css);

        // to file
        $destination = tempnam(sys_get_temp_dir(),'/unittest');
        $sass->setArguments('--sourcemap=none'); // disable source map comments
        $sass->compile(__DIR__ . '/sassCode.sass', $destination);
        $this->assertEquals(file_get_contents(__DIR__ . '/sassOutput.css'), trim(file_get_contents($destination)));
        unlink($destination);
        
        // compressed to standard output
        $sass->setCompress(true);
        $css = $sass->compile(__DIR__ . '/sassCode.sass');
        $this->assertEquals(file_get_contents(__DIR__ . '/sassOutputCompress.css'), $css);
    }
}