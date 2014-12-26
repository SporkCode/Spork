<?php
/**
 * 
 * Spork Zend Framework 2 Library
 * 
 * @author Chris Schreiber <chris@sporkcode.com>
 */

namespace Spork\View\Helper\Style;

use Zend\Form\View\Helper\AbstractHelper;
use Zend\View\Resolver\TemplatePathStack;
use Zend\View\Resolver\AggregateResolver;
use Zend\View\Resolver\ResolverInterface;

/**
 * View Helper renders Stylus file as inline CSS
 */
class Stylus extends AbstractHelper
{
    /**
     * Template resolver used to find Stylus files
     * 
     * @var \Zend\View\Resolver\TemplatePathStack
     */
    protected $resolver;
    
    /**
     * Renders Stylus file as inline CSS
     * 
     * @param string $template Name of stylus file
     * @throws \Exception on template unresolved
     * @return string
     */
    public function __invoke($template)
    {
        if (null === $this->resolver) {
            $this->initializeResolver();
        }
        
        $file = $this->resolver->resolve($template);
        if (false === $file) {
            throw new \Exception("Failed resolving Stylus template '$template'");
        }
        $file = escapeshellarg($file);
        $result = exec("/usr/local/bin/stylus --print $file 2>&1", $output, $exitCode);
        if ($exitCode != 0) {
            throw new \Exception(sprintf("Failed compiling Stylus CSS: %s", $result));
        }
        return '<style>' . implode('', $output) . '</style>';
    }
    
    /**
     * Initializes Resolver to find Stylus files by mimicking the default template resolver. 
     */
    protected function initializeResolver()
    {
        $resolver = $this->resolver = new TemplatePathStack();
        $resolver->setDefaultSuffix('styl');
        
        $setPaths = function($source) use (&$setPaths, $resolver) {
            if ($source instanceof TemplatePathStack) {
                foreach ($source->getPaths() as $path) {
                    $resolver->addPath($path);
                }
            } elseif ($source instanceof AggregateResolver) {
                foreach ($source as $child) {
                    $setPaths($child);
                }
            }
        };
        $setPaths($this->getView()->resolver());
    }
}