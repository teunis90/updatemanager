<?php

namespace AppBundle\Classes;

use AppBundle\Classes\PdoBulkSubquery;

class PdoBulk {
	
	private $pdo = null;
	private $queue = null;
	private $autoflush = 0;
	
	public function __construct($pdo = null) {
		if(!is_null($pdo)) {
			$this->setPdo($pdo);
		}
	}
	
	// Make sure all records are persisted
	public function __destruct() {
		if($this->queue) {
			foreach($this->queue as $table => $entries) {
				$this->flushQueue($table);
			}
		}
	}
	
	public function injectPdo($pdo) {
		$this->setPdo($pdo);
	}
	
	// Bulk insert logic
	public function flushQueue($table, $onduplicate = null) {
		if(gettype($table) != 'string') throw new Exception('First parameter should be string, ' . gettype($table) . ' given.');
		if(isset($this->queue[$table]) && $this->queue[$table]) {
			// Define query
			$first = true;
			$query = "INSERT INTO `".$table."` (`" . implode("`,`", array_keys($this->queue[$table][0])) . "`) VALUES ";
			
			// Count number of parameters in element
			$prefixOuter = '';
			$rowPlaces = '';
			foreach($this->queue[$table] as $entry) {
				$prefixInner = '';
				$rowPlaces .= $prefixOuter . '(';
				foreach($entry as $column) {
					if(is_object($column) && $column instanceof PdoBulkSubquery) {
						$rowPlaces .= $prefixInner . '(' . $column->getQuery() . ')';
					} else {
						$rowPlaces .= $prefixInner . '?';
					}
					$prefixInner = ',';
				}
				$rowPlaces .= ')';
				$prefixOuter = ',';
			}
			
			$query .= $rowPlaces;
			if($onduplicate && gettype($onduplicate) == 'string') {
				$query .= ' ' . $onduplicate;
			}
			
			$stmt = $this->getPdo()->prepare($query);
			
			// Prepare binding values for execution
			$values = array();
			foreach ($this->queue[$table] as $entry) {
				foreach($entry as $column_name => $column_value) {
					if(is_object($column_value) && $column_value instanceof PdoBulkSubquery) {
						unset($entry[$column_name]);
					}	
				}
				$values = array_merge($values, array_values($entry));
			}
			
			$stmt->execute($values);
			$this->queue[$table] = array();
			return true;
		} else {
			return false;
		}
	}
	
	private function isAssoc($arr)
	{
		if(gettype($arr) != 'array') throw new Exception('First parameter should be array, ' . gettype($arr) . ' given.');
	    return array_keys($arr) !== range(0, count($arr) - 1);
	}
	
	public function getPdo() {
		return $this->pdo;
	}

	public function setPdo($pdo) {
		$this->pdo = $pdo;
	}
	
	public function getQueue($table) {
		if(isset($this->queue[$table]) && $this->queue[$table] !== false) {
			return $this->queue[$table];
		} else {
			return false;
		}
	}
	
	public function getQueueLength($table) {
		if(isset($this->queue[$table]) && $this->queue[$table] !== false) {
			return count($this->queue[$table]);
		} else {
			return false;
		}
	}
	
	public function persist($table, $entry) {
		if(gettype($table) != 'string') throw new Exception('First parameter should be string, ' . gettype($table) . ' given.');
		if($ret = $this->isAssoc($entry)) {
			$this->queue[$table][] = $entry;
			if($this->getQueueLength($table) > $this->getAutoflush() && $this->getAutoflush() != 0) {
				$this->flushQueue($table);
			}
		}
		return $ret;
	}
	
	public function getAutoflush() {
		return $this->autoflush;
	}

	public function setAutoflush($autoflush) {
		if(gettype($autoflush) != 'integer') throw new Exception('First parameter should be integer, ' . gettype($autoflush) . ' given.');
		$this->autoflush = $autoflush;
	}
}