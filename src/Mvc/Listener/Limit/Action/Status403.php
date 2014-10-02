<?php
/**
 * 
 * Spork Zend Framework 2 Library
 * 
 * @author Chris Schreiber <chris@sporkcode.com>
 */
namespace Spork\Mvc\Listener\Limit\Action;

use Zend\Mvc\MvcEvent;

/**
 * Sets the response status to 403 Forbiden.
 * 
 * IMPORTANT: This will not prevent access to anything by itself, but should 
 * be used with other listeners to handle response status
 * @see \Spork\Mvc\Listener\AccessDeniedStrategy
 */
class Status403 implements ActionInterface
{
    /**
     * Sets the response status to 403 Forbiden
     * 
     * @see \Spork\Mvc\Listener\Limit\Action\ActionInterface::__invoke()
     * @param MvcEvent $event
     */
    public function __invoke(MvcEvent $event)
    {
        $event->getResponse()->setStatusCode(403);
    }
}