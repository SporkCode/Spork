<?php
/**
 * Spork Zend Framework 2 Library
 * 
 * @author Chris Schreiber <chris@sporkcode.com>
 */

namespace Spork\Filter;

/**
 * Filter a value by a list of possible values
 * 
 * If value is not in list the default value (null by default) is returned. 
 */
class InArray extends AbstractFilter
{
    /**
     * @var mixed Default value to return if filtered value is not in list
     */
    protected $default;
    
    /**
     * @var array List of values to test filtered value against
     */
    protected $values = array();
    
    /**
     * @param array $values
     * @param mixed $default
     */
    public function __construct(array $values = null, $default = null)
    {
        if (null !== $values) {
            $this->setValues($values);
        }
        
        if (null !== $default) {
            $this->setDefault($default);
        }
    }
    
    /**
     * Filter value
     * 
     * If value is in list of valid values value is returned. Otherwise default
     * value is returned.
     * 
     * @param mixed $value
     * @return mixed
     */
    public function filter($value)
    {
        if (in_array($value, $this->values)) {
            return $value;
        }
        return $this->default;
    }
    
    /**
     * Set default value
     * 
     * @param mixed $default
     */
    public function setDefault($default)
    {
        $this->default = $default;
    }
    
    /**
     * Get default value
     * 
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }
    
    /**
     * Set filter values
     * 
     * @param array $values
     */
    public function setValues(array $values)
    {
        $this->values = $values;
    }
    
    /**
     * Get filter values
     * 
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }
}