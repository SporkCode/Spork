<?php
/**
 * 
 * Spork Zend Framework 2 Library
 * 
 * @author Chris Schreiber <chris@sporkcode.com>
 */

namespace Spork\CSS;

/**
 * Wrapper class for Less CSS preprocessor (http://lesscss.org/)
 * 
 * Looks for lessc executable on system path. You can specify a full path to
 * the compiler.
 * 
 * If the class is created as a Service Manager factory it will look for
 * configuration options under 'css-less'.
 */
class Less extends AbstractCompiler
{
    protected $compiler = 'lessc';

    protected $configurationKey = 'css-less';
    
    protected $extension = array('less');
    
    
    protected function getCommandArguments($source, $destination, array $includes)
    {
        $arguments = '';
        
        if ($this->compress) {
            $arguments .= ' --compress';
        }
        
        if (!empty($includes)) {
            $arguments .= ' --include-path ' . escapeshellarg(implode(':', $includes));
        }
        
        $arguments .= ' ' . escapeshellarg($source);

        if (null != $destination) {
            $arguments .= ' ' . escapeshellarg($destination);
        }
        
        return $arguments;
    }
}