<?php
/**
 * 
 * Spork Zend Framework 2 Library
 * 
 * @author Chris Schreiber <chris@sporkcode.com>
 */
namespace Spork\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * Strip whitespace from between HTML tags
 */
class TagTrim extends AbstractHelper
{
    /**
     * Remove whitespace from between HTML tags
     * 
     * @param string $html
     */
    public function __invoke($html)
    {
        return preg_replace('`>\s+<`', '><', $html);
    }
}