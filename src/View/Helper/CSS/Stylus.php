<?php
/**
 *
 * Spork Zend Framework 2 Library
 *
 * @author Chris Schreiber <chris@sporkcode.com>
 */
namespace Spork\View\Helper\CSS;

/**
 * Convert Stylus (http://learnboost.github.io/stylus/) scripts into inline 
 * style sheets.
 */
class Stylus extends AbstractHelper
{
    protected $compiler = 'cssStylus';
}