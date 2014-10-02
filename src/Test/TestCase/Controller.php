<?php
/**
 * 
 * Spork Zend Framework 2 Library
 * 
 * @author Chris Schreiber <chris@sporkcode.com>
 */
namespace Spork\Test\TestCase;

use Zend\Mvc\Controller\AbstractController;
use Zend\Stdlib\RequestInterface;
use Zend\Stdlib\ResponseInterface;
use Zend\Http\PhpEnvironment\Request;
use Zend\Http\PhpEnvironment\Response;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\ParametersInterface;
use Zend\Stdlib\Parameters;
use Zend\Mvc\Router\Http\RouteMatch;
use Zend\Mvc\View\Console\ViewManager;
use Zend\View\Renderer\PhpRenderer;
use Zend\Console\Console;

/**
 * PHPUnit Test Case for testing controller actions
 */
class Controller extends Db
{
    /**
     * Is console state
     * 
     * @var Boolean
     */
    protected $isConsole;
    
	/**
	 * List of request parameters
	 * @var array
	 */
	protected $params;
	
	/**
	 * Request object
	 * 
	 * @var \Zend\Http\PhpEnvironment\Request
	 */
	protected $request;
	
	/**
	 * Response object
	 * 
	 * @var \Zend\Http\PhpEnvironment\Response
	 */
	protected $response;
	
	/**
	 * Setup resources for dispatching a controller action
	 * 
	 * @see \Spork\Test\TestCase\Db::setUp()
	 */
	protected function setUp()
	{
		parent::setUp();
		
		$this->isConsole = Console::isConsole();
		Console::overrideIsConsole(false);
		
		$serviceManager = $this->getServiceManager();
		
		$this->params = array();
		$this->request = $serviceManager->get('request');
		$this->response = $serviceManager->get('response');
	}
	
	/**
	 * Dispatch a controller action
	 * 
	 * @param string $controller
	 * @param string $action
	 * @return mixed
	 */
	protected function dispatch($controller, $action = null)
	{
	    $serviceManager = $this->getServiceManager();
	    
		if ($controller instanceof AbstractController) {
			$controllerName = get_class($controller);
		} else {
			$controllerName = $controller;
			$controllerLoader = $serviceManager->get('controllerLoader');
			$controller = $controllerLoader->get($controller);
		}

	    $request = $this->request; 
	    $response = $this->response;
	    
	    $application = $serviceManager->get('application');
	    $application->bootstrap();
	    $event = $application->getMvcEvent();
	    
	    $controller->setEvent($event);
		
		$params = $this->params;
		$params['controller'] = $controllerName;
		if (null != $action) {
			$params['action'] = $action;
		}
	    $routeMatch = new RouteMatch($params);
	    $event->setRouteMatch($routeMatch);

		$router = $serviceManager->get('router');
		$router->setRequestUri($request->getUri());
	    $event->setRouter($router);
	    
		return $controller->dispatch($request, $response);
	}
	
	/**
	 * Reset is console flag
	 * 
	 * @see \Spork\Test\TestCase\Service::tearDown()
	 */
	protected function tearDown()
	{
	    Console::overrideIsConsole($this->isConsole);
	}
	
	/**
	 * Test that a response was created 
	 */
	protected function assertResponse()
	{
		$this->assertInstanceOf('Zend\Http\Response', $this->response);
	}
	
	/**
	 * Test that response has status 200 OK 
	 */
	protected function assertResponseOk()
	{
		$this->assertResponseStatusCode('200',
				"Failed asserting response status OK");
	}
	
	/**
	 * Test that response has specific status code
	 * 
	 * @param integer $code
	 * @param string $message
	 */
	protected function assertResponseStatusCode($code, $message = null)
	{
		$this->assertResponse();
		$this->assertEquals($code, $this->response->getStatusCode(), $message);
	}
	
	/**
	 * Test that response has status 302 Redirect and optionally that the 
	 * location header is set to a specific URL.
	 * 
	 * @param string $url
	 */
	protected function assertResponseRedirect($url = null)
	{
	    $this->assertResponseStatusCode(302, "Failed asserting response status is redirect");
	    $this->assertTrue($this->response->isRedirect(), "Failed asserting response is redirect");
	    if (null !== $url) {
	        $location = $this->response->getHeaders()->get('location')->getFieldValue();
	        $this->assertEquals($url, $location, "Failed asserting redirect location '$location' is '$url'");
	    }
	}
	
	/**
	 * Test that response has status 403 Forbidden
	 */
	protected function assertResponseAccessDenied()
	{
		$this->assertResponseStatusCode(403,
				"Failed asserting response status access denied.");
	}
	
	/**
	 * Test that response has status 500 Application Error 
	 */
	protected function assertResponseError()
	{
		$this->assertResponseStatusCode(500,
				"Failed asserting response status application error.");
	}
	
	/**
	 * Test that response has status 404 Page Not Found 
	 */
	protected function assertResponseNotFound()
	{
		$this->assertResponseStatusCode(404,
				"Failed asserting response status not found.");
	}
	
	/**
	 * Get authenticated user
	 * 
	 * @deprecated This is application specific and should be removed or made
	 * more generic
	 */
	protected function getAuthMember()
	{
	    return $this->getServiceManager()->get('authMember');
	}
	
	/**
	 * Get a value or list of values from the request's query values
	 * 
	 * @param string $name
	 * @param string $default
	 * @return ParametersInterface | mixed
	 */
	protected function getQuery($name = null, $default = null)
	{
		return $this->getRequest()->getQuery($name, $default);
	}
	
	/**
	 * Get a value or list of values from the request's post values
	 * 
	 * @param string $name
	 * @param string $default
	 * @return \Zend\Stdlib\ParametersInterface | mixed
	 */
	protected function getPost($name = null, $default = null)
	{
		return $this->getRequest()->getPost($name, $default);
	}
	
	/**
	 * Get the Request object
	 * 
	 * @return \Zend\Http\PhpEnvironment\Request
	 */
	protected function getRequest()
	{
		return $this->request;
	}
	
	/**
	 * Get the Response object
	 * 
	 * @return \Zend\Http\PhpEnvironment\Response
	 */
	protected function getResponse()
	{
		return $this->response;
	}
	
	/**
	 * Set authenticated member
	 * @deprecated this is application specific and should be removed or made
	 * more generic
	 * @param unknown $member
	 */
	protected function setAuthMember($member)
	{
		$auth = $this->getServiceManager()->get('auth');
		$auth->authenticate(new \Itt\Lib\Authentication\Adapter\Dummy($member));
	}
	
	/**
	 * Set Request method
	 * 
	 * @param string $method GET, POST, etc
	 */
	protected function setMethod($method)
	{
	    $this->getRequest()->setMethod($method);
	}
	
	/**
	 * Set requests query values
	 * 
	 * @param array|ParametersInterface $query
	 */
	protected function setQuery($query)
	{
		if (!$query instanceof ParametersInterface) {
			$query = new Parameters($query);
		}
		$this->getRequest()->setQuery($query);
	}
	
	/**
	 * Set route parameters
	 * 
	 * @param array $params
	 */
	protected function setParams(array $params)
	{
		$this->params = $params;
	}
	
	/**
	 * Set requests post values
	 * 
	 * @param array|ParametersInterface $post
	 */
	protected function setPost($post)
	{
		if (!$post instanceof ParametersInterface) {
			$post = new Parameters($post);
		}
		$this->getRequest()->setPost($post);
		$this->getRequest()->setMethod(Request::METHOD_POST);
	}
	
	/**
	 * Set Request object
	 * 
	 * @param RequestInterface $request
	 */
	protected function setRequest(RequestInterface $request)
	{
		$this->request = $request;
	}
	
	/**
	 * Set Response object
	 * 
	 * @param ResponseInterface $response
	 */
	protected function setResponse(ResponseInterface $response)
	{
		$this->response = $response;
	}
}