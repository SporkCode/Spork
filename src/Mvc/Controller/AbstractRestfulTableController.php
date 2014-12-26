<?php
namespace Spork\Mvc\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Zend\Json\Json;
use Zend\Db\Adapter\Adapter;

abstract class AbstractRestfulTableController extends AbstractRestfulController
{
	protected $start;
	
	protected $end;
	
	/**
	 * Get table gateway instance
	 * 
	 * @return \Zend\Db\TableGateway\AbstractTableGateway 
	 */
	abstract protected function getTable();
	
	public function setStart($start)
	{
		$this->start = $start;
	}
	
	public function getStart()
	{
		if (null === $this->start) {
			$this->importRange();
		}
		return $this->start;
	}
	
	public function setEnd($end)
	{
		$this->end = $end;
	}
	
	public function getEnd()
	{
		if (null === $this->end) {
			$this->importRange();
		}
		return $this->end;
	}
	
	protected function getListJsonResponse(Select $select = null)
	{
		$start = $this->getStart();
		$end = $this->getEnd();
		$count = $this->getListCount($select);
		if ($count < $end) {
			$end = $count;
		}
		$this->setContentRange($start, $end, $count);
		
		$data = $this->getListData($select);
		$this->response->setContent(Json::encode($this->toArray($data)));
		return $this->response;
	}
	
	protected function getListCount(Select $select = null)
	{
		$table = $this->getTable();
		$select = null === $select ? $table->getSql()->select() : clone $select;
		$select
			->reset('offset')
			->reset('limit')
			->reset('order')
			->columns(array('count' => new Expression('count(*)')));
		$db = $table->getAdapter();
		$result = $db->query($select->getSqlString($db->platform), Adapter::QUERY_MODE_EXECUTE);
		//$result = $table->selectWith($select);
		return $result->current()->count;
	}
	
	protected function getListData(Select $select = null)
	{
		$table = $this->getTable();
		$select = null === $select ? $table->getSql()->select() : clone $select;
		$start = $this->getStart();
		$end = $this->getEnd();
		$select
			->offset((int) $start)
			->limit((int) $end - $start + 1);
		$data = $table->selectWith($select);
		return $data;
	}
	
	protected function importRange()
	{
		$header = $this->request->getHeader('Range');
		if (!$header || !preg_match('`items=([0-9]+)-([0-9]+)`', $header->getFieldValue(), $matches)) {
			$this->setRangeDefaults();
		} else {
			$this->start = $matches[1];
			$this->end = $matches[2];
		}
	}
	
	protected function setRangeDefaults()
	{
		$this->start = 0;
		$this->end = 25;
	}
	
	protected function setContentRange($start, $end, $total)
	{
		$this->response->getHeaders()->addHeaderLine(sprintf(
				'Content-Range: items %d-%d/%d', $start, $end, $total));
	}
	
	/**
	 * Convert a result set to an array
	 * 
	 * @param unknown $data
	 */
	protected function toArray($data)
	{
	    if (is_array($data)) {
	        return $data;
	    }
	    
	    if (is_object($data)) {
	        if (method_exists($data, 'toArray')) {
	            return $data->toArray();
	        }
	        if ($data instanceof \Traversable) {
	            return iterator_to_array($data);
	        }
	    }
	    
	    return (array) $data;
	}
}