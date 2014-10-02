<?php
/**
 * 
 * Spork Zend Framework 2 Library
 * 
 * @author Chris Schreiber <chris@sporkcode.com>
 */
namespace Spork\DateTime;

/**
 * Extend DateInterval class to add functionality and fix problems with old 
 * version.
 */
class DateInterval extends \DateInterval
{
    /**
     * Does this PHP version have quirks
     * 
     * @var boolean
     */
    protected static $quirkMode;
    
    /**
     * Initialize class
     * 
     * @param string|DateInterval $interval_spec
     */
    public function __construct($interval_spec) {
        if ($interval_spec instanceof \DateInterval) {
            $this->y = $interval_spec->y;
            $this->m = $interval_spec->m;
            $this->d = $interval_spec->d;
            $this->h = $interval_spec->h;
            $this->i = $interval_spec->i;
            $this->s = $interval_spec->s;
            $this->invert = $interval_spec->invert;
            $this->days = self::isQuirkMode() && $interval_spec->days == -99999 ? false : $interval_spec->days;
        } else {
            parent::__construct($interval_spec);
        }
    }
    
    /**
     * Convert interval to number of seconds
     * @return integer
     */
    public function toSeconds()
    {
        $seconds = $this->s;
        $seconds += $this->i * 60;
        $seconds += $this->h * 3600;
        
        if ($this->days) {
            $seconds += $this->days * 86400;
        } else {
            if ($this->d) {
                $seconds += $this->d * 86400;
            }
            if ($this->m) {
                trigger_error('Calculating seconds for interval with months property. Result may not be accurate.', E_USER_WARNING);
                $seconds += 2629800;
            }
            if ($this->y) {
                trigger_error('Calculating seconds for interval with years property. Result may not be accurate.', E_USER_WARNING);
                $seconds += $this->y * 31557600;
            }
        }
        return $seconds;
    }
    
    /**
     * Test if PHP version has quirks
     * @return boolean
     */
    protected static function isQuirkMode()
    {
        if (null === self::$quirkMode) {
            self::$quirkMode = version_compare(PHP_VERSION, '5.5.4', '<') 
                    && version_compare(PHP_VERSION, '5.5.0', '>') 
                    || version_compare(PHP_VERSION, '5.4.20', '<');
        }
        return self::$quirkMode;
    }
}