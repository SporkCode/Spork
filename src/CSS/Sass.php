<?php
/**
 * 
 * Spork Zend Framework 2 Library
 * 
 * @author Chris Schreiber <chris@sporkcode.com>
 */

namespace Spork\CSS;

/**
 * Wrapper class for Sass CSS preprocessor (http://sass-lang.com/)
 * 
 * Looks for sass executable on system path. You can specify a full path for
 * the compiler.
 * 
 * If the class is created as a Service Manager factory it will look for
 * configuration options under 'css-sass'.
 */
class Sass extends AbstractCompiler
{
    protected $compiler = 'sass';

    protected $configurationKey = 'css-sass';
    
    protected $extension = array('sass', 'scss');
    
    protected function getCommandArguments($source, $destination = null)
    {
        $arguments = '';
        
        if ($this->compress) {
            $arguments .= ' --style=compressed';
        }
        
        $includePath = $this->findIncludePath($source);
        if (null !== $includePath) {
            $arguments .= ' --load-path ' . escapeshellarg($includePath);
        }
        
        $arguments .= ' ' . escapeshellarg($source);

        if (null != $destination) {
            $arguments .= ' ' . escapeshellarg($destination);
        }
        
        return $arguments;
    }
}