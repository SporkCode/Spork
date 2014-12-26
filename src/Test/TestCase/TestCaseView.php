<?php
/**
 * 
 * Spork Zend Framework 2 Library
 * 
 * @author Chris Schreiber <chris@sporkcode.com>
 */
namespace Spork\Test\TestCase;

use Zend\View\Model\ViewModel;
use Zend\View\ViewEvent;
use Zend\View\Strategy\PhpRendererStrategy;
use Zend\View\Model\ModelInterface;

/**
 * PHPUnit test case for testing view templates
 */
class TestCaseView extends TestCaseDb
{
    /**
     * View Model
     * 
     * @var \Zend\View\Model\ModelInterface
     */
    protected $model = 'Zend\View\Model\ViewModel';
    
    /**
     * Output of rendered template
     * 
     * @var string
     */
    protected $response;
    
    /**
     * Initialize View Model instance
     * 
     * @see \Spork\Test\TestCase\TestCaseDb::setUp()
     */
    protected function setUp()
    {
        parent::setUp();
        
        if (!$this->model instanceof ModelInterface) {
            $this->model = new $this->model();
        }
    }
    
    /**
     * Render a template
     * 
     * @param string $template
     */
    protected function render($template = null)
    {
        $application = $this->getApplication();
        $application->bootstrap();

        $model = $this->model;
        if (null !== $template) {
            $model->setTemplate($template);
        }
        
        $mvcEvent = $application->getMvcEvent();
        
        $view = $this->getViewManager()->getView();
        $view->setRequest($mvcEvent->getRequest());
        $view->setResponse($mvcEvent->getResponse());
        
        // force view to return output instead of triggering response event
        $model->setOption('has_parent', true);
        
        $this->response = $view->render($model);
    }
    
    /**
     * Remove View Model
     * 
     * @see \Spork\Test\TestCase\TestCaseService::tearDown()
     */
    protected function tearDown()
    {
        $this->model = get_class($this->model);
    }
    
    /**
     * Add a variable to the View Model
     * 
     * @param string $name
     * @param mixed $value
     */
    protected function addVariable($name, $value)
    {
        $this->model->setVariable($name, $value);
    }

    /**
     * Set a list of variables in the View Model
     * @param array $variables
     */
    protected function setVariables(array $variables)
    {
        $this->model->setVariables($variables);
    }
    
    /**
     * Add a child View Model to top View Model
     * 
     * @param ViewModel $child
     * @param string $captureTo
     * @param string $append
     */
    protected function addChild($child, $captureTo = null, $append = false)
    {
        $this->model->addChild($child, $captureTo, $append);
    }
    
    /**
     * Get application
     * 
     * @return \Zend\Mvc\Application
     */
    protected function getApplication()
    {
        return $this->services->get('application');
    }
    
    /**
     * Get view manager
     * 
     * @return \Zend\Mvc\View\Http\ViewManager
     */
    protected function getViewManager()
    {
        return $this->services->get('viewManager');
    }
}