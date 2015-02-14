<?php
/**
 *
 * Spork Zend Framework 2 Library
 *
 * @author Chris Schreiber <chris@sporkcode.com>
 */
namespace Spork\View\Helper\CSS;

use Zend\View\Helper\AbstractHelper as BaseClass;
use Spork\CSS\AbstractCompiler;
use Zend\View\Resolver\ResolverInterface;
use Zend\View\Resolver\AggregateResolver;
use Zend\View\Resolver\TemplatePathStack;

/**
 * Base class for CSS View Helpers
 */
abstract class AbstractHelper extends BaseClass
{
    /**
     * Compiler instance or service name to use
     * 
     * @var \Spork\CSS\AbstractCompiler | string
     */
    protected $compiler;
    
    /**
     * Resolver instance to use
     * 
     * @var \Zend\View\Resolver\ResolverInterface
     */
    protected $resolver;
    
    /**
     * Invoking the class will either compile a template and return the CSS or
     * return itself if not template is specified.
     * 
     * @param string $template Optional template to compile
     * @return \Spork\View\Helper\CSS\AbstractHelper|string
     */
    public function __invoke($template = null)
    {
        if (null == $template) {
            return $this;
        }
        
        return $this->compile($template);
    }
    
    /**
     * Compile template
     * 
     * @param string $template
     * @return string CSS
     */
    public function compile($template)
    {
        $file = $this->getResolver()->resolve($template);
        $css = $this->getCompiler()->compile($file);
        return "<style>$css</style>";
    }
    
    /**
     * Get compiler instance
     * 
     * @throws \Exception On compiler not set or invalid compiler type found 
     * @return \Spork\CSS\AbstractCompiler
     */
    public function getCompiler()
    {
        if (!$this->compiler instanceof AbstractCompiler) {
            if (null === $this->compiler) {
                throw new \Exception('Compiler not set');
            }
            $compiler = $this->getView()->getHelperPluginManager()->getServiceLocator()->get($this->compiler);
            if (!$compiler instanceof AbstractCompiler) {
                throw new \Exception(sprintf(
                    'Invalid compiler type: %s not instance of Spork\CSS\AbstractCompiler',
                    is_object($compiler) ? get_class($compiler) : gettype($compiler)));
            }
            $this->compiler = $compiler;
        }
        
        return $this->compiler;
    }
    
    /**
     * Set compiler instance or name of service
     * 
     * @param \Spork\CSS\AbstractCompiler|string $compiler
     * @return \Spork\View\Helper\CSS\AbstractHelper
     */
    public function setCompiler($compiler)
    {
        $this->compiler = $compiler;
        return $this;
    }
    
    /**
     * Get resolver instance. If resolver has not been set a default resolver
     * is created.
     * 
     * @return \Zend\View\Resolver\ResolverInterface
     */
    public function getResolver()
    {
        if (null === $this->resolver) {
            $this->initializeResolver();
        }
        return $this->resolver;
    }
    
    /**
     * Set Resolver instance
     * 
     * @param ResolverInterface $resolver
     * @return \Spork\View\Helper\CSS\AbstractHelper
     */
    public function setResolver(ResolverInterface $resolver)
    {
        $this->resolver = $resolver;
        return $this;
    }

    /**
     * Initializes Resolver to find files by mimicking the default template 
     * resolver and using compiler extensions.
     */
    protected function initializeResolver()
    {
        $extensions = $this->getCompiler()->getExtensions();
        $resolver = $this->resolver = new AggregateResolver();
    
        $stack = array($this->getView()->resolver());
        while ($source = array_shift($stack)) {
            if ($source instanceof AggregateResolver) {
                foreach ($source as $child) {
                    array_push($stack, $child);
                }
            } elseif ($source instanceof TemplatePathStack) {
                foreach ($extensions as $extension) {
                    $child = clone $source;
                    $child->setDefaultSuffix($extension);
                    $resolver->attach($child);
                }
            } else {
                $resolver->attach($source);
            }
        }
    }
}