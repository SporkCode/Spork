<?php
/**
 * 
 * Spork Zend Framework 2 Library
 * 
 * @author Chris Schreiber <chris@sporkcode.com>
 */
namespace Spork\Mvc\Listener;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;

/**
 * Disables layouts on XML HTTP Requests
 */
class XmlHttpRequestStrategy extends AbstractListenerAggregate
{
	/**
	 * Attach listeners
	 * 
	 * @see \Zend\EventManager\ListenerAggregateInterface::attach()
	 * @param EventManagerInterface $events
	 */
	public function attach(EventManagerInterface $events)
	{
		$sharedEvents = $events->getSharedManager();
		$this->listeners[] = $sharedEvents->attach(
				'Zend\Mvc\Controller\AbstractController', 
				MvcEvent::EVENT_DISPATCH, 
				array($this, 'handleXmlHttpRequest'), 
				-99);
	}
	
	/**
	 * If request is an XML HTTP Request disable layouts
	 * 
	 * @param MvcEvent $event
	 */
	public function handleXmlHttpRequest(MvcEvent $event)
	{
		$request = $event->getRequest();
		if ($request->isXMLHttpRequest()) {
			$dispatchResult = $event->getResult();
			if ($dispatchResult instanceof ViewModel) {
				$dispatchResult->setTerminal(true);
			}
		}
	}
}