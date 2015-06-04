<?php
namespace IttTest\View\Renderer;

use Spork\Test\TestCase\TestCase;
use Spork\View\Renderer\Icalendar;

class iCalendarTest extends TestCase
{
    public function testIcalendar()
    {
        $renderer = new Icalendar();
        $ical = $renderer->render('test', array(
            'events' => array(
                array(),
            )
        ));
        $expected = "BEGIN:VCALENDAR\r\n" .
            "PRODID:-//SporkCode//Spork//iCalendar//EN\r\n" . 
            "VERSION:2.0\r\n" .
            "BEGIN:VEVENT\r\n" .
            "END:VEVENT\r\n" .
            "END:VCALENDAR\r\n";
        $this->assertEquals($expected, $ical);
    }
}