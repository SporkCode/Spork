<?php
namespace IttTest\View\Renderer;

use Spork\Test\TestCase\TestCase;
use Spork\view\Renderer\Icalendar;

class iCalendarTest extends TestCase
{
    public function testIcalendar()
    {
        $renderer = new Icalendar();
        $ical = $renderer->render('test', array(
            'events' => array(
                
            )
        ));
        $this->assertEquals('', $ical);
    }
}