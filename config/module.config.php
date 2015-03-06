<?php
/**
 * 
 * Spork Zend Framework 2 Library
 * 
 * @author Chris Schreiber <chris@sporkcode.com>
 */
return array(
    'service_manager' => array(
        'abstract_factories' => array(),
        'aliases' => array(),
        'factories' => array(
            'cssLess' => 'Spork\CSS\Less',
            'cssSass' => 'Spork\CSS\Sass',
            'cssStylus' => 'Spork\CSS\Stylus',
        ),
        'invokables' => array(),
    ),
    'view_helpers' => array(
        'invokables' => array(
            'date' => 'Spork\View\Helper\Date',
            'headScript' => 'Spork\View\Helper\HeadScript',
            'inlineScript' => 'Spork\View\Helper\InlineScript',
            'cssLess' => 'Spork\View\Helper\CSS\Less',
            'cssSass' => 'Spork\View\Helper\CSS\Sass',
            'cssStylus' => 'Spork\View\Helper\CSS\Stylus',
            'tagTrim' => 'Spork\View\Helper\TagTrim',
            'timeElapsed' => 'Spork\View\Helper\TimeElapsed',
        ),
        'factories' => array(
            'dojo' => 'Spork\View\Helper\Dojo',
            'googleAnalytic' => 'Spork\View\Helper\GoogleAnalytic',
        )
    )
);
