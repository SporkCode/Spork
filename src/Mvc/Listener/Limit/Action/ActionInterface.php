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
 * Action Interface
 */
interface ActionInterface
{
    /**
     * Take action
     * 
     * @param MvcEvent $event
     */
    public function __invoke(MvcEvent $event);
}