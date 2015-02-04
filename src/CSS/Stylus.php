<?php
/**
 * 
 * Spork Zend Framework 2 Library
 * 
 * @author Chris Schreiber <chris@sporkcode.com>
 */

namespace Spork\CSS;

/**
 * Wrapper class for Stylus CSS preprocessor (http://learnboost.github.io/stylus/)
 * 
 * Looks for stylus executable on system path. You can specify a full path
 * for the compiler.
 * 
 * If the class is created as a Service Manager factory it will look for 
 * configuration options under 'css-stylus'.
 */
class Stylus extends AbstractCompiler
{
    protected $compiler = 'stylus';

    protected $configurationKey = 'css-stylus';
    
    protected $extensions = array('styl');
    
    protected function getCommandArguments($source, $destination, array $includes)
    {
        $arguments = '';
        
        if ($this->compress) {
            $arguments .= ' --compress';
        }
        
        foreach ($includes as $include) {
            $arguments .= ' --include ' . escapeshellarg($include);
        }
        
        if (null == $destination) {
            $arguments .= ' --print ' . escapeshellarg($source);
        } else {
            if (is_dir($destination)) {
                $arguments .= ' ' . escapeshellarg($source) . ' --out ' . escapeshellarg($destination);
            } else {
                $arguments .= ' < ' . escapeshellarg($source) . ' > ' . escapeshellarg($destination);
            }
        }

        return $arguments;
    }
}