<?php
/**
 * 
 * Spork Zend Framework 2 Library
 * 
 * @author Chris Schreiber <chris@sporkcode.com>
 */
namespace Spork\View\Helper;

/**
 * Extends HeadScript to add integration for Dojo helper
 */
class HeadScript extends \Zend\View\Helper\HeadScript
{
    /**
     * Find an initialize Dojo helper before rendering head scripts
     * 
     * @see \Zend\View\Helper\HeadScript::toString()
     * @param string $indent
     * @return string
     */
    public function toString($indent = null)
    {
        $helperManager = $this->getView()->getHelperPluginManager();
        if ($helperManager->has('dojo')) {
            $dojo = $helperManager->get('dojo');
            $dojo->initialize();
        }
        return parent::toString($indent);
    }
}