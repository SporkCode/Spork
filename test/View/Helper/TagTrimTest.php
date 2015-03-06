<?php
namespace SporkTest\View\Helper;

use Spork\Test\TestCase\TestCase;
use Spork\View\Helper\TagTrim;

class TagTrimTest extends TestCase
{
    public function testTagTrim()
    {
        $html = <<<HDOC
<ul>
    <li>one</li>
    <li>two</li>
    <li>three</li>
</ul>
HDOC;
        
        $tagTrim = new TagTrim();
        $output = $tagTrim($html);
        
        $this->assertEquals(<<<HDOC
<ul><li>one</li><li>two</li><li>three</li></ul>
HDOC
            , $output);
    }
}