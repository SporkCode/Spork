<?php
/**
 * 
 * Spork Zend Framework 2 Library
 * 
 * @author Chris Schreiber <chris@sporkcode.com>
 */
namespace Spork\View\Helper;

use Zend\Form\View\Helper\AbstractHelper;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Render Google Analytics tracking code. 
 * 
 * The helper adds the code to the InlineScript view helper when it is 
 * initialized. It is important that the helper is only initialized once.
 * This can be done automatically by using the Spork InlineScript view helper
 * which initialized this helper when it is rendered.  
 */
class GoogleAnalytic extends AbstractHelper implements FactoryInterface
{
    /**
     * Has the helper been initialized
     * 
     * @var boolean
     */
    protected $initialized = false;
    
    /**
     * The Google tracking ID
     * 
     * @var string
     */
    protected $trackingId;

    /**
     * Create and configure helper instance
     *
     * @see \Zend\ServiceManager\FactoryInterface::createService()
     * @param ServiceLocatorInterface $viewHelperManager
     * @return \Spork\View\Helper\GoogleAnalytic
     */
    public function createService(ServiceLocatorInterface $viewHelperManager)
    {
        $serviceLocator = $viewHelperManager->getServiceLocator();
        $config = $serviceLocator->has('config') ? $serviceLocator->get('config') : array();
        if (isset($config['google_analytics'])) {
            $this->config($config['google_analytics']);
        }
    
        return $this;
    }
    
    /**
     * Set Google Analytics tracking ID
     *
     * @param string $id
     */
    public function setTrackingId($id)
    {
        $this->trackingId = (string) $id;
    }
    
    /**
     * Get Google Analytics tracking ID
     * 
     * @return string
     */
    public function getTrackingId()
    {
        return $this->trackingId;
    }
    
    /**
     * Configure helper
     * 
     * @param array $options
     */
    public function config(array $options)
    {
        foreach ($options as $key => $value) {
            $method = 'set' . $key;
            if (method_exists($this, $method)) {
                call_user_func(array($this, $method), $value);
            }
        }
    }
    
    /**
     *  Inject Google Analytics tracker code into InlineScript helper. This
     *  should only be called once after the helper is completely configured.
     */
    public function initialize()
    {
        if (true == $this->initialized) {
            return;
        }
        $this->initialized = true;
        
        if (null !== $this->trackingId) {
            $trackerId = $this->trackingId;
            $inlineScript = $this->getView()->plugin('inlineScript');
            $inlineScript->appendScript(<<<HDOC
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
ga('create', '$trackerId', 'auto');
ga('send', 'pageview');
HDOC
            );
        }
    }
}