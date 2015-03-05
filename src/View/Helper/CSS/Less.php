<?php
/**
 *
 * Spork Zend Framework 2 Library
 *
 * @author Chris Schreiber <chris@sporkcode.com>
 */
namespace Spork\View\Helper\CSS;

/**
 * Convert Less (http://lesscss.org/) scripts into inline style sheets
 */
class Less extends AbstractHelper
{
    protected $compiler = 'cssLess';
}