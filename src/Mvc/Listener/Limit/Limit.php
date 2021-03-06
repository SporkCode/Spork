<?php
/**
 * 
 * Spork Zend Framework 2 Library
 * 
 * @author Chris Schreiber <chris@sporkcode.com>
 */
namespace Spork\Mvc\Listener\Limit;

use Spork\DateTime\DateInterval;

/**
 * Defines a type of event to keep track of, the maximum times the event is 
 * allowed to occur in an interval and the actions to take when the threshold
 * is exceeded.
 */
class Limit
{
    /**
     * Event name
     * 
     * @var string
     */
    protected $name;
    
    /**
     * Maximum number of times and event is allowed to occur
     * 
     * @var integer
     */
    protected $limit = 10;
    
    /**
     * The interval to measure limit over
     * 
     * @var DateInterval
     */
    protected $interval = 'P1D';
    
    /**
     * List of ActionInterface objects to invoke when limit is exceeded
     * 
     * @var array
     */
    protected $actions = array();
    
    /**
     * Initialize instance
     * 
     * @param string $name
     * @param int $limit
     * @param DateInterval|string $interval
     * @param array $actions
     */
    public function __construct($name, $limit = null, $interval = null, array $actions = null)
    {
        $this->name = $name;
        if (null !== $limit) {
            $this->setLimit($limit);
        }
        $this->setInterval(null == $interval ? $this->interval : $interval);
        if (null !== $actions) {
            $this->setActions($actions);
        }
    }
    
    /**
     * Get limit name
     * 
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Get maximum count for this limit
     * 
     * @return integer
     */
    public function getLimit()
    {
        return $this->limit;
    }
    
    /**
     * Set the maximum count for this limit
     * 
     * @param int $limit
     * @return \Spork\Mvc\Listener\Limit\Limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }
    
    /**
     * Get the limit interval
     * 
     * @return \Spork\DateTime\DateInterval
     */
    public function getInterval()
    {
        return $this->interval;
    }
    
    /**
     * Set the limit interval
     * 
     * @param string|DateInterval $interval
     * @return \Spork\Mvc\Listener\Limit\Limit
     */
    public function setInterval($interval)
    {
        $this->interval = $interval instanceof DateInterval ? $interval : new DateInterval($interval);
        return $this;
    }
    
    /**
     * Get actions
     * 
     * @return array List of Action objects
     */
    public function getActions()
    {
        return $this->actions;
    }
    
    /**
     * Set actions
     * 
     * @param array $actions List of Action object
     */
    public function setActions(array $actions)
    {
        $this->actions = array();
        foreach ($actions as $action) {
            $this->addAction($action);
        }
    }
    
    /**
     * Add an action
     * 
     * @param Action\ActionInterface $action
     * @return \Spork\Mvc\Listener\Limit\Limit
     */
    public function addAction(Action\ActionInterface $action)
    {
        $this->actions[] = $action;
        return $this;
    }
}