<?php
/**
 * 
 * Spork Zend Framework 2 Library
 * 
 * @author Chris Schreiber <chris@sporkcode.com>
 */

namespace Spork\CSS;

/**
 *
 */
class Sass extends AbstractCompiler
{
    protected $compiler = '/usr/local/bin/sass';
    
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