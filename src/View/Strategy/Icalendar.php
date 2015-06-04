<?php
namespace Spork\View\Strategy;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\View\ViewEvent;
use Spork\View\Model\Icalendar as IcalendarViewModel;
use Spork\view\Renderer\Icalendar as IcalendarRenderer;
use Spork\view\Renderer\Spork\view\Renderer;

class Icalendar extends AbstractListenerAggregate
{
    public function attach(EventManagerInterface $events)
    {
        $sharedEvents = $events->getSharedManager();
        $this->listeners[] = $sharedEvents->attach(
            'Zend\View\View', 
            ViewEvent::EVENT_RENDERER, 
            array($this, 'renderer'),
            2);
        $this->listeners[] = $sharedEvents->attach(
            'Zend\View\View', 
            ViewEvent::EVENT_RESPONSE, 
            array($this, 'response'),
            2);
    }
    
    public function renderer(ViewEvent $event)
    {
        $model = $event->getModel();
        if ($model instanceof IcalendarViewModel) {
            return new IcalendarRenderer();
        }
    }
    
    public function response(ViewEvent $event)
    {
        $renderer = $event->getRenderer();
        if ($renderer instanceof IcalendarRenderer) {
            $event->getResponse()->getHeaders()->addHeaderLine(
                'Content-type', 
                'text/calendar; charset=utf-8');
        }
    }
}