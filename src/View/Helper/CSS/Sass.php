<?php
/**
 *
 * Spork Zend Framework 2 Library
 *
 * @author Chris Schreiber <chris@sporkcode.com>
 */
namespace Spork\View\Helper\CSS;

/**
 * Convert Sass (http://sass-lang.com/) scripts into inline style sheets
 */
class Sass extends AbstractHelper
{
    protected $compiler = 'cssSass';
}