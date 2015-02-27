<?php
namespace Spork\Db\Sql;

use Zend\Db\Sql\Select as BaseClass;
use Zend\Db\Sql\Predicate\PredicateSet;

class Select extends BaseClass
{
    public static function factory($conditions)
    {
        if ($conditions instanceof BaseClass) {
            return $conditions;
        }
        
        $select = new self();
        
        if (is_array($conditions) || $conditions instanceof \Traversable) {
            foreach ($conditions as $name => $condition) {
                switch ($name) {
                    case 'columns':
                        if (is_array($condition) 
                                && array_key_exists(0, $condition) 
                                && is_array($condition[0])) {
                            call_user_func_array(array($select, 'columns'), $condition);
                            continue;
                        }
                        $select->columns($condition);
                        break;
                    case 'combine':
                        if (is_array($condition)) {
                            call_user_func_array(array($select, 'combine'), $condition);
                            continue;
                        }
                        $select->combine($condition);
                        break;
                    case 'from':
                        $select->from($condition);
                        break;
                    case 'group':
                        $select->group($condition);
                        break;
                    case 'having':
                        if (is_array($condition) 
                                && array_key_exists(1, $condition)
                                && ($condition[1] == PredicateSet::OP_AND 
                                    || $condition[1] == PredicateSet::OP_OR)) {
                            call_user_func_array(array($select, 'having'), $condition);
                            continue;
                        }
                        $select->having($condition);
                        break;
                    case 'join':
                        call_user_func_array(array($select, 'join'), $condition);
                        break;
                    case 'limit':
                        $select->limit($condition) ;
                        break;
                    case 'offset':
                        $select->offset($condition);
                        break;
                    case 'order':
                        $select->order($condition);
                        break;
                    case 'where':
                        if (is_array($condition) 
                                && array_key_exists(1, $condition)
                                && ($condition[1] == PredicateSet::OP_AND 
                                    || $condition[1] == PredicateSet::OP_OR)) {
                            call_user_func_array(array($select, 'where'), $condition);
                            continue;
                        }
                        $select->where($condition);
                        break;
                    default:
                        throw new \Exception("Invalid condition ($name)");
                }
            }
            
            return $select;
        }
        
        throw new \Exception(sprintf('Invalid conditions type (%s)',
            is_object($conditions) ? get_class($conditions) : gettype($conditions)));
    }
}