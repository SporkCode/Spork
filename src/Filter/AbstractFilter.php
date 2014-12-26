<?php
namespace Spork\Filter;

use Zend\Filter\AbstractFilter as BaseClass;

abstract class AbstractFilter extends BaseClass
{
    public static function inline($value, $options = array())
    {
        $filter = new static();
        $filter->setOptions($options);
        return $filter->filter($value);
    }
}