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
class Less extends AbstractCompiler
{
    protected $compiler = '/usr/local/bin/lessc';
    
    protected $extension = array('less');
    
    protected function getCommandArguments($source, $destination = null)
    {
        $arguments = '';
        
        if ($this->compress) {
            $arguments .= ' --compress';
        }
        
        $includePath = $this->findIncludePath($source);
        if (null !== $includePath) {
            $arguments .= ' --include-path ' . escapeshellarg($includePath);
        }
        
        $arguments .= ' ' . escapeshellarg($source);

        if (null != $destination) {
            $arguments .= ' ' . escapeshellarg($destination);
        }
        
        return $arguments;
    }
}