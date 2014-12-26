<?php
namespace Spork\Filter;

class InArray extends AbstractFilter
{
    protected $default;
    
    protected $values = array();
    
    public function __construct(array $values = null, $default = null)
    {
        if (null !== $values) {
            $this->setValues($values);
        }
        
        if (null !== $default) {
            $this->setDefault($default);
        }
    }
    
    public function filter($value)
    {
        if (in_array($value, $this->values)) {
            return $value;
        }
        return $this->default;
    }
    
    public function setDefault($default)
    {
        $this->default = $default;
    }
    
    public function setValues(array $values)
    {
        $this->values = $values;
    }
}