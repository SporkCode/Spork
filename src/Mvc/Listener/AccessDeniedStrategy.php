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
use Zend\Stdlib\ResponseInterface;
use Zend\View\Model\ViewModel;

/**
 * Displays an access denied error page if the response status is 403.
 * 
 * If the MvcEvent::Response status has been set to 403 it creates a View Model
 * to render the access denied template, inserts it into the MvcEvent and ends
 * propagation of the dispatch event.
 * 
 * If self::renderLayout is true it will also inject the access denied View Model
 * into the layout View Model. IMPORTANT make sure your layout never contains any
 * sensitive information before you enable this.
 * 
 * Configuration Example
 * array(
 *     'access_denied_strategy' => array(
 *         'renderLayout' => BOOLEAN,
 *         'template' => template/alias/or/path,
 *     )
 * );
 */
class AccessDeniedStrategy extends AbstractListenerAggregate
{
    const CONFIG_KEY = 'access_denied_strategy';

    /**
     * Should access denied template be rendered inside layout.
     * 
     * @var boolean
     */
    protected $renderLayout = false;
    
    /**
     * Alias or path to access denied template
     * 
     * @var string
     */
    protected $template = 'error/403';
    
    /**
     * Attach event
     * 
     * @see \Zend\EventManager\ListenerAggregateInterface::attach()
     * @param EventManagerInterface $events
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, array($this, 'injectAccessDeniedModel'), 1000);
    }
    
    /**
     * Get render layout flag
     * 
     * @return boolean
     */
    public function getRenderLayout()
    {
        return $this->renderLayout;
    }
    
    /**
     * Set render layout flag
     * 
     * @param boolean $flag
     * @return \Spork\Mvc\Listener\AccessDeniedStrategy
     */
    public function setRenderLayout($flag)
    {
        $this->renderLayout = (boolean) $flag;
        return $this;
    }
    
    /**
     * Get access denied template
     * 
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }
    
    /**
     * Set access denied template
     * 
     * @param unknown $template
     * @return \Spork\Mvc\Listener\AccessDeniedStrategy
     */
    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }
    
    /**
     * Inject access denied View Model is response status is 403
     * 
     * @param MvcEvent $event
     */
    public function injectAccessDeniedModel(MvcEvent $event)
    {
        $this->config($event);
        
        $result = $event->getResult();
        
        if ($result instanceof ResponseInterface) {
            return;
        }
        
        $response = $event->getResponse();
        
        if ($response->getStatusCode() != 403) {
            return;
        }
        
        $event->setError('Access Denied');
        $event->stopPropagation(true);
        
        $model = new ViewModel();
        $model->setTemplate($this->template);
        
        if ($this->renderLayout == true) {
            $layout = $event->getViewModel();
            $layout->addChild($model);
        } else {
            $model->setTerminal(true);
            $event->setViewModel($model);
        }
    }
    
    /**
     * Set configuration options from application configuration
     * 
     * @param MvcEvent $event
     */
    protected function config(MvcEvent $event)
    {
        $appConfig = $event->getApplication()->getServiceManager()->get('config');
        $config = array_key_exists(self::CONFIG_KEY, $appConfig) ? $appConfig[self::CONFIG_KEY] : array();
        
        if (array_key_exists('template', $config)) {
            $this->setTemplate($config['template']);
        }
        
        if (array_key_exists('renderLayout', $config)) {
            $this->setRenderLayout($config['renderLayout']);
        }
    }
}