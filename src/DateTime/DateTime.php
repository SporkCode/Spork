<?php
/**
 * 
 * Spork Zend Framework 2 Library
 * 
 * @author Chris Schreiber <chris@sporkcode.com>
 */
namespace Spork\DateTime;

/**
 * Extends DateTime class to extend functionality
 */
class DateTime extends \DateTime
{
    /**
     * Initialize instance
     * 
     * @param string $time
     * @param string $object
     */
    public function __construct($time = null, $object = null)
    {
        if ($time instanceof \DateTime) {
            $time = $time->getTimestamp();
        }
        parent::__construct($time, $object);
    }
    
    /**
     * Override function to return Spork/DateTime/DateInterval instance.
     * 
     * @see DateTime::diff()
     * @param \DateTimeInterface $object
     * @param boolean $absolute
     * @return \Spork\DateTime\DateInterval
     */
    public function diff($object, $absolute = null)
    {
        return new DateInterval(parent::diff($object, $absolute));
    }
    
    /**
     * Measure the amount of time elapsed from this DateTime to now.
     * 
     * @param boolean $absolute
     * @return \Spork\DateTime\DateInterval
     */
    public function elapsed($absolute = null)
    {
        return new DateInterval(date_create()->diff($this, $absolute));
    }
}