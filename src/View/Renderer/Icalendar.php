<?php
namespace Spork\view\Renderer;

use Zend\View\Renderer\RendererInterface;
use Zend\View\Model\ModelInterface;

class Icalendar implements RendererInterface
{
    public $events = 'events';
    
    public $todos = 'todos';
    
    public $journals = 'journals';
    
    public $freebusy = 'freebusy';
    
    protected $calendar = array(
        'name' => 'VCALENDAR',
        'properties' => array(
            'calscale',
            'method',
            'prodid',
            'version',
            'x-prop',
        )
    );
    
    protected $event = array(
        'name' => 'VEVENT',
        'properties' => array(
            'attach',
            'attendee',
            'categories',
            'class',
            'comment',
            'contact',
            'create',
            'description',
            'dtend' => array('filter' => 'formatDateTime'),
            'dtstamp' => array('filter' => 'formatDateTime'),
            'dtstart' => array('filter' => 'formatDateTime'),
            'duration',
            'exdate',
            'exrule',
            'geo',
            'last-mod',
            'location',
            'organizer',
            'priority',
            'rdate',
            'recurid',
            'related',
            'resources',
            'rrule',
            'rstatus',
            'seq',
            'status',
            'summary',
            'transp',
            'uid',
            'url',
            'x-prop',
        )
    );
    
    protected $todo = array(
        'attach',
        'attendee',
        'categories',
        'class',
        'comment',
        'completed',
        'contact',
        'created',
        'description',
        'dtstamp' => array('filter' => 'formatDateTime'),
        'dtstart' => array('filter' => 'formatDateTime'),
        'due',
        'duration',
        'exdate',
        'exrule',
        'geo',
        'last-mod',
        'location',
        'organizer',
        'percent',
        'priority',
        'rdate',
        'recurid',
        'related',
        'resources',
        'rrule',
        'rstatus',
        'seq',
        'status',
        'summary',
        'uid',
        'url',
        'x-prop',
    );

    protected $journal = array(
        'attach',
        'attendee',
        'categories',
        'class',
        'comment',
        'contact',
        'created',
        'description',
        'dtstamp' => array('filter' => 'formatDateTime'),
        'dtstart' => array('filter' => 'formatDateTime'),
        'exdate',
        'exrule',
        'last-mod',
        'organizer',
        'rdate',
        'recurid',
        'related',
        'rrule',
        'rstatus',
        'seq',
        'status',
        'summary',
        'uid',
        'url',
        'x-prop',
    );

    protected $freebusy = array(
        'attendee',
        'comment',
        'contact',
        'dtend' => array('filter' => 'formatDateTime'),
        'dtstamp' => array('filter' => 'formatDateTime'),
        'dtstart' => array('filter' => 'formatDateTime'),
        'duration',
        'freebusy',
        'organizer',
        'rstatus',
        'uid',
        'url',
        'x-prop',
    );
    
    protected $timezone = array(
        'last-mod',
        'tzid',
        'tzurl',
    );
    
    public function render($nameOrModel, $values = null)
    {
        if (null == $values && $nameOrModel instanceof ModelInterface) {
            $values = $nameOrModel->getVariables();
        }
        if (!isset($values['version'])) {
            $values['version'] = '2.0';
        }
        if (!isset($values['prodid'])) {
            $values['prodid'] = '-//SporkCode//Spork//iCalendar//EN';
        }
        
        $output = null;
        
        if (isset($values['events'])) {
            foreach ($values['events'] as $event) {
                $output .= $this->renderComponent('event', $event);
            }
        }
        
        if (isset($values['todos'])) {
            foreach ($values['todos'] as $todo) {
                $output .= $this->renderComponent('todo' ,$todo);
            }
        }

        if (isset($values['journals'])) {
            foreach ($values['journals'] as $journal) {
                $output .= $this->renderComponent('journal' ,$journal);
            }
        }

        if (isset($values['freebusy'])) {
            foreach ($values['freebusy'] as $freebusy) {
                $output .= $this->renderComponent('freebusy' ,$freebusy);
            }
        }

        if (isset($values['timezones'])) {
            foreach ($values['timezones'] as $timezone) {
                $output .= $this->renderComponent('timezone' ,$timezone);
            }
        }
        
        if (null === $output) {
            throw new \Exception('iCalendar requires at least one event, todo, journal, freebusy or timezone component');
        }
        
        $output = $this->renderComponent('calendar', $output);
        return $output;
    }
    
    protected function renderComponent($name, $values)
    {
        $meta = $this->$name;
        $output = 'BEGIN:' . $meta['name'] . "\r\n";
        if (is_string($values)) {
            $output .= $values;
        } else {
            foreach ($meta['properties'] as $name => $options) {
                if (is_string($options)) {
                    $name = $options;
                    $options = array();
                }
                if (isset($values[$name])) {
                    $value = $values[$name];
                    if (isset($options['filter'])) {
                        $value = call_user_func(array($this, $options['filter']), $value);
                    }
                    $output .= strtoupper($name) . ':' . $value . "\r\n";
                }
            }
        }
        $output .= 'END:' . $meta['name'] . "\r\n";
        return $output;
    }
    
    protected function formatDateTime($datetime)
    {
        if (is_scalar($datetime)) {
            if (is_numeric($datetime)) {
                $datetime = '@' . $datetime;
            }
            $datetime = new \DateTime($datetime);
        }
        
        if (!$datetime instanceof \DateTime) {
            throw new \Exception('Invalid date time');
        }
        
        return $datetime->format('Ymd\Tgis\Z');
    }
}