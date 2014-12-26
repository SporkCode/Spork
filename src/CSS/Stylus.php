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
class Stylus extends AbstractCompiler
{
    protected $compiler = '/usr/local/bin/stylus';
    
    protected $extensions = array('styl');
    
    protected function getCommandArguments($source, $destination = null)
    {
        $arguments = '';
        
        if ($this->compress) {
            $arguments .= ' --compress';
        }
        
        $includePath = $this->findIncludePath($source);
        if (null !== $includePath) {
            $arguments .= ' --include ' . escapeshellarg($includePath);
        }
        
        if (null == $destination) {
            $arguments .= ' --print ' . escapeshellarg($source);
        } else {
            if (is_dir($destination)) {
                $arguments .= escapeshellarg($source) . ' --out ' . escapeshellarg($destination);
            } else {
                $arguments .= ' < ' . escapeshellarg($source) . ' > ' . escapeshellarg($destination);
            }
        }

        return $arguments;
    }
}