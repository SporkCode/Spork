<?php
/**
 * 
 * Spork Zend Framework 2 Library
 * 
 * @author Chris Schreiber <chris@sporkcode.com>
 */
namespace Spork\Mvc\Listener;

use Spork\View\Model\Identity as IdentityViewModel;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;

/**
 * Injects a child view model into the layout to display authentication / identity info.
 * The view model renders the template layout/identity and makes it available in the
 * layout template as the property 'identity'.
 * 
 * layout/layout.phtml
 * ...
 * <body>
 *     <?php echo $this->identity ?>
 *     <?php echo $this->content ?>
 * </body>
 * ...
 * 
 * The listener retrives an authentication service and identity model from the
 * service manager and makes them available in the identity template as the
 * properties 'auth' and 'identity'
 * 
 * layout/identity.phtml
 * <?php if ($this->auth->hasIdentity()): ?>
 *     <?php echo $this->identity->name; ?>
 * <?php else: ?>
 *     <a href="/login">Log In</a>
 * <?php endif: ?>
 */
class ViewModelIdentity extends AbstractListenerAggregate
{
	/**
	 * Attach listeners
	 * 
	 * @see \Zend\EventManager\ListenerAggregateInterface::attach()
	 * @param EventManagerInterface $events
	 * @param number $priority
	 */
	public function attach(EventManagerInterface $events, $priority = 1)
	{
		$this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER, 
				array($this, 'injectIdentityModel'), $priority);
	}
	
	/**
	 * Inject identity view model into layout
	 * 
	 * @param MvcEvent $event
	 * @throws \Exception
	 */
	public function injectIdentityModel(MvcEvent $event)
	{
	    $viewModel = $event->getViewModel();
	    if ($viewModel->getTemplate() == 'layout/layout') {
	        $servies = $event->getApplication()->getServiceManager();
	        $appConfig = $servies->get('config');
	        if (isset($appConfig['view_model_identity'])) {
	            $config = $appConfig['view_model_identity'];
	        } else {
	            throw new \Exception('view_model_identity key not found in configuration');
	        }
	        if (!$servies->has($config['authenticationService'])) {
	            throw new \Exception('Auththentication service not found');
	        }
	        if (!$servies->has($config['identity'])) {
	            throw new \Exception('Identity not found');
	        }
	        $childViewModel = new IdentityViewModel(array(
	            'auth'     => $servies->get($config['authenticationService']),
	            'identity' => $servies->get($config['identity']),
	        ));
	        if (isset($config['template'])) {
	            $childViewModel->setTemplate($config['template']);
	        }
	        if (isset($config['captureTo'])) {
	            $childViewModel->setCaptureTo($config['captureTo']);
	        }
	        $viewModel->addChild($childViewModel);
	    }
	}
}