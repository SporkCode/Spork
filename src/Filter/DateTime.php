<?php
/**
 * Spork Zend Framework 2 Library
 * 
 * @author Chris Schreiber <chris@sporkcode.com>
 */

namespace Spork\Filter;

/**
 * Convert a value into a Datetime object. Return false if conversion fails.
 */
class DateTime extends AbstractFilter
{
    /**
     * filter
     * @see \Zend\Filter\FilterInterface::filter()
     * @param mixed $value
     * @return \DateTime|boolean
     */
    public function filter($value)
    {
        if ($value instanceof \DateTime) {
            return $value;
        }
        
        try {
            $datetime = new \DateTime($value);
        } catch (\Exception $exception) {
            return false;
        }
        
        return $datetime;
    }
}