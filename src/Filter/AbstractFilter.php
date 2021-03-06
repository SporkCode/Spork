<?php
/**
 * Spork Zend Framework 2 Library
 * 
 * @author Chris Schreiber <chris@sporkcode.com>
 */

namespace Spork\Filter;

use Zend\Filter\AbstractFilter as BaseClass;

/**
 * Extend Zend\Filter\AbstractFilter
 */
abstract class AbstractFilter extends BaseClass
{
    /**
     * Static function to execute filter
     * 
     * @param int $value
     * @param array $options
     * @return mixed
     */
    public static function inline($value, $options = array())
    {
        $filter = new static();
        $filter->setOptions($options);
        return $filter->filter($value);
    }
}