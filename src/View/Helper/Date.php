<?php
/**
 * 
 * Spork Zend Framework 2 Library
 * 
 * @author Chris Schreiber <chris@sporkcode.com>
 */
namespace Spork\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Render a DateTime object as a formated string
 */
class Date extends AbstractHelper implements FactoryInterface
{
    /**
     * Predefined formats
     * @var array
     */
    protected $formats = array(
        'long' => 'l F jS Y g:i:s A e',
        'medium' => 'D M jS y g:i a e',
        'short' => 'm/d/y g:i a',
        'dateLong' => 'l F jS Y e',
        'dateMedium' => 'D M jS y e',
        'dateShort' => 'm/d/y',
        'timeLong' => 'g:i:s A e',
        'timeShort' => 'g:i a',
    );
    
    /**
     * Render a DateTime object as a formated string.
     * 
     * @param \DateTimeInterface $datetime
     * @param string $format
     */
    public function __invoke($datetime, $format = 'medium')
    {
        $datetime = $this->normalize($datetime);
        
        if (array_key_exists($format, $this->formats)) {
            $format = $this->formats[$format];
        }
        
        return $datetime->format($format);
    }
    
    /**
     * Create and configure instance
     * 
     * @see \Zend\ServiceManager\FactoryInterface::createService()
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $appConfig = $serviceLocator;
        $config = array_key_exists($appConfig['view_helper_date']) ? $appConfig['view_helper_date'] : array();
        if (array_key_exists('formats', $config)) {
            $this->addFormats($config['formats']);
        }
    }
    
    /**
     * Set a datetime format
     * 
     * @param string $name
     * @param string $format
     */
    public function setFormat($name, $format)
    {
        $this->formats[$name] = $format;
    }
    
    /**
     * Get a datetime format
     * 
     * @param string $name
     * @return string
     */
    public function getFormat($name)
    {
        return $this->formats[$name];
    }
    
    /**
     * Set a list of datetime formats
     * @param array $formats
     */
    public function setFormats(array $formats)
    {
        $this->formats = $formats;
    }
    
    /**
     * Add datetime formats
     * @param array $formats
     */
    public function addFormats(array $formats)
    {
        foreach ($formats as $name => $format) {
            $this->formats[$name] = $format;
        }
    }
    
    /**
     * Get datetime formats
     * @return array
     */
    public function getFormats()
    {
        return $this->formats;
    }
    
    protected function normalize($datetime)
    {
        if ($datetime instanceof \DateTimeInterface) {
            return $datetime;
        }
        if (is_numeric($datetime)) {
            $datetime = new \DateTime('@' . $datetime);
            $datetime->setTimezone(new \DateTimeZone(date_default_timezone_get()));
            return $datetime;
        }
        if (is_string($datetime)) {
            return new \DateTime($datetime);
        }
        
        throw new \Exception('Invalid date time type (%s)', gettype($datetime));
    }
}