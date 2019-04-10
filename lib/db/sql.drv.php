<?php
/**
 *
 *  class CSQLDriver : sql.drv.php
 * 
 * Абстрактный класс драйвера базы данных
 * 
 * 
 * @package kernel.drv.db
 *
 * @author    Alex Roldugin <alex.roldugin@gmail.com>
 * @author    Andrew Roldugin <andrew.roldugin@gmail.com>
 * 
 * @copyright (c) Roldugin Brothers, 2006
 */

  /**
   * @todo Add return values
   */
abstract class CSQLDriver
{
  /**
   * @var string Database Host
   */
  var $host = '';

  /**
   * @var string Database User
   */
  var $user = '';
  /**
   * @var string Database Password
   */
  var $password = '';
  
  /**
   * @var string Database Name
   */
  var $database = '';

  /**
   * @var bool Datase Connection type
   */
  var $persistent = false;
	
  /**
   * @var mixed $conn Database connection desriptor 
   */
  var $conn = NULL;
	
  /**
   * @var resource $result Query Result 
   */
  var $result = false;

  var $lastQuery = "";

  /**
   * Constructor of Class
   *
   * @param string $host database host
   * @param string $user database user
   * @param string $password database password
   * @param string database database name
   * @param string persistent connection type
   *
   */
  function CSQLDriver($host, $user, $password, $database, $persistent = false)
    {
      $this->host = $host;
      $this->user = $user;
      $this->password = $password;
      $this->database = $database;
      $this->persistent = $persistent;
    }

  /**
   * Try to connect to database using connection settings
   */
  abstract function connect();
	
  /**
   * try to disconnect from MySQL server
   */
  abstract function disconnect();

  /**
   * retrun error message
   */
  abstract function error();
  
  /**
   * execute query $sql
   * @param string $sql query to execute
   */
  abstract function execQuery($sql);

  /**
   * secure item from SQL-injections
   */
  abstract function secureItem($item);

  /**
   * returns number of affected rows
   * @return integer number of affected rows
   */
  abstract function affectedRows();

  /**
   * returns number of rows in query result
   * @return integer number of rows, returned from query
   */
  abstract function numRows();

  /**
   * retruns Last inserted ID
   * @return integer last inserted ID
   */
  abstract function insertedId();

  /**
   * get query results as object
   * @return integer result as object 
   */
  abstract function fetchObject();
  
  /**
   * get query results as assoc array
   * @return assoc array on success false - on fail
   */
  abstract function fetchAssoc();

  /**
   * @return array result as array
   */
  abstract function getArray();
  
  /**
   * free results
   * @return int 0 - on fail, else - on success
   */
  abstract function freeResult();

  /**
   * rename current database
   * @param string $newName DataBase new name
   * @return int 0 - on success, else - on fail
   */
  abstract function renameDb($newName);

  /**
   * add column to table 
   * @param string $tableName table name
   * @param string $columnParams new column parameters
   * @return int 0 - on success, else - on fail
   */
  abstract function addColumn($tableName, $columnParams);

  /**
   * drop column from table
   * @param string $tableName table name
   * @param string $columnName columnName
   * @return int 0 - on success, else - on fail
   */
  abstract function dropColumn($tableName, $columnName);

  /**
   * rename column in table
   * @param string $tableName table name
   * @param string $oldName column current name
   * @param string $newName column new name
   * @return int 0 - on success, else - on fail
   */
  abstract function renameColumn($tableName, $oldName, $newName);
  
  /**
   * rename table
   * @param string $oldName table current name
   * @param string $newName table new name
   * @return int 0 - on success, else - on fail
   */
  abstract function renameTable($oldName, $newName);

  /**
   * create current database dump
   * @todo add variable description
   */
  abstract function createDump($dumpFile, $dumpFormat, $dbCharset, $useDropTables, $quoteNames, $completeInsert, $extendedInsert, $noDatas);

  /**
   * import current database dump
   * @todo add variable description
   */
  abstract function importDump($dumpFile, $dumpFormat);

  /**
   * Generate Queries-Log
   */
  function log( $lighter = null ) {
    if( !$lighter ) {
      ob_start();
      print_r( $this->_queries );
      $result = ob_get_contents();
      ob_end_clean();
      
      return $result;
    } else {
      //dBug::dump( $lighter );
      return $lighter->hi( $this->_queries );
    }
    
  }


}//db
?>