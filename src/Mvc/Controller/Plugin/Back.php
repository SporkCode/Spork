<?php
/**
 * 
 * Spork Zend Framework 2 Library
 * 
 * @author Chris Schreiber <chris@sporkcode.com>
 */

namespace Spork\Mvc\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Mvc\MvcEvent;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\EventManagerInterface;

/**
 * Allows controller to redirect to last route. Individual routes can be 
 * ignored or multiple routes can be blacklisted to prevent application from 
 * going back to those routes.
 */
class Back extends AbstractPlugin implements ListenerAggregateInterface
{

    const CONFIG_KEY = 'controller_plugin_back';

    /**
     * Flag to ignore current route
     * 
     * @var boolean
     */
    protected $ignore = false;

    /**
     * List of routes to ignore
     * 
     * @var array
     */
    protected $blacklist = array();

    /**
     * List of listeners
     * 
     * @var array
     */
    protected $listeners = array();

    /**
     * Attach listeners
     *
     * @see \Zend\EventManager\ListenerAggregateInterface::attach()
     * @param EventManagerInterface $events            
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, 
            array(
                $this,
                'injectPlugin'
            ), 100);
        $this->listeners[] = $events->attach(MvcEvent::EVENT_FINISH, 
            array(
                $this,
                'storeRoute'
            ), - 100);
    }

    /**
     * Detach listeners
     *
     * @see \Zend\EventManager\ListenerAggregateInterface::detach()
     * @param EventManagerInterface $events            
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $listener) {
            $events->detach($listener);
        }
    }

    /**
     * Get list of blacklist routes
     * 
     * @return array:
     */
    public function getBlacklist()
    {
        return $this->blacklist;
    }

    /**
     * Set list of blacklist routes
     * 
     * @param array $blacklist            
     */
    public function setBlacklist(array $blacklist)
    {
        $this->blacklist = array();
    }

    /**
     * Ignore matched route to prevent plugin from returning to this page
     * 
     * @param string $flag            
     */
    public function ignore($flag = true)
    {
        $this->ignore = (boolean) $flag;
    }

    /**
     * Redirect to last route
     * 
     * @param string $default
     *            Default route to use if previous route not found
     */
    public function redirect($default = 'home')
    {
        $redirect = $this->getController()->plugin('redirect');
        $route = $this->getSession()->route;
        if (null === $route) {
            return $redirect->toRoute($default);
        }
        return $redirect->toRoute($route->getMatchedRouteName(), 
            $route->getParams());
    }

    /**
     * Get last route URL
     * 
     * @param string $default            
     */
    public function url($default = 'home')
    {
        $url = $this->getController()->plugin('url');
        $route = $this->getSession()->route;
        if (null === $route) {
            return $url->fromRoute($default);
        }
        return $url->fromRoute($route->getMatchedRouteName(), 
            $route->getParams());
    }

    /**
     * Inject instance into controller plugin manager.
     * This ensures controller plugin
     * is same instance as event listener.
     *
     * @param MvcEvent $event            
     */
    public function injectPlugin(MvcEvent $event)
    {
        $event->getApplication()
            ->getServiceManager()
            ->get('controllerPluginManager')
            ->setService('back', $this);
    }

    /**
     * Store route in session so it can be returned to later.
     * @param MvcEvent $event            
     */
    public function storeRoute(MvcEvent $event)
    {
        if ($this->ignore) {
            return;
        }
        
        $request = $event->getRequest();
        if ($request->isXmlHttpRequest()) {
            return;
        }
        
        $appConfig = $event->getApplication()
            ->getServiceManager()
            ->get('config');
        $config = array_key_exists(self::CONFIG_KEY, $appConfig) ? $appConfig[self::CONFIG_KEY] : array();
        if (array_key_exists('blacklist', $config)) {
            $this->setBlacklist($config['blacklist']);
        }
        
        $routeMatch = $event->getRouteMatch();
        if (null === $routeMatch ||
             in_array($routeMatch->getMatchedRouteName(), $this->blacklist)) {
            return;
        }
        
        $this->getSession()->route = $routeMatch;
    }

    /**
     * Get session
     * 
     * @return \Zend\Session\Container
     */
    protected function getSession()
    {
        return new \Zend\Session\Container(__CLASS__);
    }
}