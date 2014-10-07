<?php
/**
 * 
 * Spork Zend Framework 2 Library
 * 
 * @author Chris Schreiber <chris@sporkcode.com>
 */
namespace Spork\View\Helper;

/**
 * Extend InlineScript to add integration for Google Analytic helper
 */
class InlineScript extends \Zend\View\Helper\InlineScript
{
    /**
     * Find and initialize GoogleAnalytic helper before rendering inline scripts.
     * 
     * @see \Zend\View\Helper\HeadScript::toString()
     * @param string $indent
     * @return string
     */
    public function toString($indent = null)
    {
        $helperManager = $this->getView()->getHelperPluginManager();
        if ($helperManager->has('googleAnalytic')) {
            $googleAnalytic = $helperManager->get('googleAnalytic');
            $googleAnalytic->initialize();
        }
        return parent::toString();
    }
}