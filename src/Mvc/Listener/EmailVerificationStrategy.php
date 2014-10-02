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
use Zend\Mvc\Router\RouteMatch;

/**
 * Checks that the authenticated member has verified their email address
 * and redirects them if they have not.
 */
class EmailVerificationStrategy extends AbstractListenerAggregate
{
    const CONFIG_KEY = 'email_verification_strategy';
    
    /**
     * Default configuration options
     * 
     * @var array
     */
    protected $defaultConfig = array(
        'authServiceName'       => 'auth',
        'identityServiceName'   => 'identity',
        'identityCheck'         => 'isEmailVerified',
        'identityCheckType'     => 'property',
        'route'                 => 'auth/verify-email',
        'routeParams'           => array(),
        'routeBlacklist'        => array(),
    );
    
    /**
     * Attach event listeners
     * 
     * @see \Zend\EventManager\ListenerAggregateInterface::attach()
     * @param EventManagerInterface $events
     */
    public function attach(EventManagerInterface $events)
    {
        $sharedEvents = $events->getSharedManager();
        $this->listeners[] = $sharedEvents->attach(
            'Zend\Stdlib\DispatchableInterface', 
            MvcEvent::EVENT_DISPATCH, 
            array($this, 'checkEmailVerification'),
            10000);
    }
    
    /**
     * Check if the email address is verified and if not redirects to a 
     * specified route.
     * 
     * @param MvcEvent $event
     */
    public function checkEmailVerification(MvcEvent $event)
    {
        $services = $event->getApplication()->getServiceManager();
        
        $appConfig = $services->get('config');
        $config = $this->defaultConfig;
        if (array_key_exists(self::CONFIG_KEY, $appConfig)) {
            $config = $appConfig[self::CONFIG_KEY] + $config;
        }
        
//         if (empty($config['routeParams'])) {
//             throw new \Exception(sprintf(
//                 "Route parameters missing. Add \"array('%s' => array('routeParams' => array(PARAMETERS)))\" to your configuration", 
//                 self::CONFIG_KEY));
//         }
        
        $routeMatch = $event->getRouteMatch();
        if (in_array($routeMatch->getMatchedRouteName(), $config['routeBlacklist'])) {
            return;
        }
        
        $auth = $services->get($config['authServiceName']);
        if (!$auth->hasIdentity()) {
            return;
        }
        
        $identity = $services->get($config['identityServiceName']);
        $isVerified = $config['identityCheckType'] == 'property' ? 
                $identity->$config['identityCheck'] :
                call_user_func(array($identity, $config['identityCheck']));
        
        if ($isVerified) {
            return;
        }
        
        //$routeMatch = new RouteMatch($config['routeParams']);
        //$event->setRouteMatch($routeMatch);
        return $services->get('controllerPluginManager')->get('redirect')->toRoute($config['route'], $config['routeParams']);
    }
}