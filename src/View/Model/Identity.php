<?php
/**
 * 
 * Spork Zend Framework 2 Library
 * 
 * @author Chris Schreiber <chris@sporkcode.com>
 */
namespace Spork\View\Model;

use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * View Model to render identity template
 */
class Identity extends \Zend\View\Model\ViewModel
{
    /**
     * Capture to name used to include template in layout
     * @var string
     */
    protected $captureTo = 'identity';
    
    /**
     * Template alias or path to render
     * @var string
     */
    protected $template = 'layout/identity';
}