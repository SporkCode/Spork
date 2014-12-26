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
        'invokables' => array(
            'cssLess' => 'Spork\CSS\Less',
            'cssSass' => 'Spork\CSS\Sass',
            'cssStylus' => 'Spork\CSS\Stylus',
        ),
    ),
    'view_helpers' => array(
        'invokables' => array(
            'date' => 'Spork\View\Helper\Date',
            'headScript' => 'Spork\View\Helper\HeadScript',
            'inlineScript' => 'Spork\View\Helper\InlineScript',
            'styleStylus' => 'Spork\View\Helper\Style\Stylus',
            'timeElapsed' => 'Spork\View\Helper\TimeElapsed'
        ),
        'factories' => array(
            'dojo' => 'Spork\View\Helper\Dojo',
            'googleAnalytic' => 'Spork\View\Helper\GoogleAnalytic',
        )
    )
);
