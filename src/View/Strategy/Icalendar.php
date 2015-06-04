<?php
namespace Spork\View\Strategy;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\View\ViewEvent;
use Spork\View\Model\Icalendar as IcalendarViewModel;
use Spork\View\Renderer\Icalendar as IcalendarRenderer;

class Icalendar extends AbstractListenerAggregate
{
    public function attach(EventManagerInterface $events)
    {
        $sharedEvents = $events->getSharedManager();
        $this->listeners[] = $sharedEvents->attach(
            'Zend\View\View', 
            ViewEvent::EVENT_RENDERER, 
            array($this, 'renderer'),
            100);
        $this->listeners[] = $sharedEvents->attach(
            'Zend\View\View', 
            ViewEvent::EVENT_RESPONSE, 
            array($this, 'response'),
            100);
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
            $response = $event->getResponse();
            $response->getHeaders()->addHeaderLine(
                'Content-type', 
                'text/calendar; charset=utf-8');
            $response->setContent($event->getResult());
        }
    }
}