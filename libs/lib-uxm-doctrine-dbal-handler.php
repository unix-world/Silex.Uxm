<?php
// Db Interface for Doctrine Dbal (Silex / Symfony)
// author: Radu Ovidiu I.
// 2015-02-21 r.150416
// License: BSD

namespace UXM;


final class Db {

	private $connection;


	public function __construct(\Doctrine\DBAL\Connection $connection) {
		//--
		$this->connection = $connection;
		//--
	} //END FUNCTION


	/*
	 * Performs a COUNT Query using Doctrine DBAL
	 *
	 * @param STRING $query The query to be executed ; parameters can be ? or :param
	 * @param ARRAY $params The query parameters: non-associative array if params are specified as ? or associative array('param' => 'value') if params are specified as :param
	 *
	 * @return Integer (the count of rows matching the query)
	 */
	public function countQuery($query, array $values=array()) {
		//--
		if(!is_array($values)) {
			throw new \Exception('ERROR: '.get_class($this).'->'.__FUNCTION__.'() expects array for parameters');
		} //end if
		//--
		$query = $this->connection->executeQuery($query, $values);
		$arr = (array) $query->fetchAll();
		//--
		$count = 0;
		//--
		if(is_array($arr[0])) {
			foreach($arr[0] as $key => $val) {
				$count = (int) $val; // find first row and first column value
				break;
			} //end if
		} //end if
		//--
		return (int) $count;
		//--
	} //END FUNCTION


	/*
	 * Performs a READ Query using Doctrine DBAL
	 *
	 * @param STRING $query The query to be executed ; parameters can be ? or :param
	 * @param ARRAY $params The query parameters: non-associative array if params are specified as ? or associative array('param' => 'value') if params are specified as :param
	 *
	 * @return Array (0..n) of the matched rows (if any) ; each row contains an associative array of the specified fields
	 */
	public function readQuery($query, array $values=array()) {
		//--
		if(!is_array($values)) {
			throw new \Exception('ERROR: '.get_class($this).'->'.__FUNCTION__.'() expects array for parameters');
		} //end if
		//--
		$query = $this->connection->executeQuery($query, $values);
		//--
		return (array) $query->fetchAll();
		//--
	} //END FUNCTION


	/*
	 * Performs a WRITE Query using Doctrine DBAL
	 *
	 * @param STRING $query The query to be executed ; parameters can be ? or :param
	 * @param ARRAY $params The query parameters: non-associative array if params are specified as ? or associative array('param' => 'value') if params are specified as :param
	 *
	 * @return Integer (the number of affected rows by the query)
	 */
	public function writeQuery($query, array $values=array()) {
		//--
		if(!is_array($values)) {
			throw new \Exception('ERROR: '.get_class($this).'->'.__FUNCTION__.'() expects array for parameters');
		} //end if
		//--
		$query = $this->connection->executeQuery($query, $values);
		//--
		return (int) $query->rowCount();
		//--
	} //END FUNCTION


} //END CLASS


//end of php code
?>