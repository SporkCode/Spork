<?php
/**
 * 
 * Spork Zend Framework 2 Library
 * 
 * @author Chris Schreiber <chris@sporkcode.com>
 */
namespace Spork\View\Helper;

use Zend\Form\View\Helper\AbstractHelper;
use Spork\DateTime\DateTime;

/**
 * View helper to render a DateTime object as the amount of time elapsed
 * since the DateTime. The precision of the elapsed time can be specified when
 * the helper is invoked.
 */
class TimeElapsed extends AbstractHelper
{

    const PRECISION_SECONDS = 'second';

    const PRECISION_MINUTES = 'minute';

    const PRECISION_HOURS = 'hour';

    const PRECISION_DAYS = 'day';

    /**
     * Render a DateTime object as the elapsed time since the DateTime.
     * 
     * The precision of the elapsed time can be specified in two ways. The second 
     * parameter sets the minimum precision to days, hours, minutes or 
     * seconds (default).
     * 
     * $timeElapsed($datetime, 'hour');
     * Renders the number of days and hours since $datetime, but ignores minutes 
     * and seconds.
     * 
     * The third parameter sets the number of levels of precision that are rendered.
     * 
     * $timeElapased($datetime, 'second', 2);
     * If the elapsed time is greater than 1 day it will render the number of days
     * and hours. If it less than 1 day but greater than 1 hour it will render the
     * number of hours and minutes.
     * 
     * @param \DateTimeInterface $datetime
     * @param string $precision Minimum precision 'day' | 'hour' | 'minute' | 'second'
     * @param int $range Precision range
     * @return string
     */
    public function __invoke(\DateTimeInterface $datetime, 
        $precision = self::PRECISION_SECONDS, $range = 4)
    {
        if (! $datetime instanceof DateTime) {
            $datetime = new DateTime($datetime);
        }
        $interval = $datetime->elapsed();
        $elapsed = '';
        
        foreach (array(
            'days' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second'
        ) as $property => $name) {
            if ($interval->$property) {
                $elapsed .= $interval->$property . ' ' 
                    . ($interval->$property == 1 ? $name : $name . 's') . ' ';
                $range --;
            }
            if ($range == 0 || $precision == $name) {
                break;
            }
        }
        
        if ($elapsed == '') {
            switch ($precision) {
                case self::PRECISION_DAYS:
                    return 'today';
                case self::PRECISION_HOURS:
                    return 'in the last hour';
                case self::PRECISION_MINUTES:
                    return 'in the last minute';
                default:
                    return 'just now';
            }
        }
        
        $elapsed .= ' ago';
        
        return $elapsed;
    }
}