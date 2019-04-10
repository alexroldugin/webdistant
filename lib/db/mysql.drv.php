<?php
/**
 * Класс интерфейса к серверу БД MySQL 
 *
 * @package kernel.drv.db
 *
 * @author    Alex Roldugin <alex.roldugin@gmail.com>
 * @author    Andrew Roldugin <andrew.roldugin@gmail.com>
 * 
 * @copyright (c) Roldugin Brothers, 2006
 * --------------------------------------------------
 */
require_once("sql.drv.php");

class db extends CSQLDriver
{
  /**
   * @var array $_queries array of executed queries
   */
  var $_queries = array();
  
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
  function db($host, $user, $password, $database, $persistent = false)
    {
      // calling base class constructor
      $this->CSQLDriver($host, $user, $password, $database, $persistent);
    }

  /**
   * Try to connect to database using connection settings
   */
  function connect()
    {
      /**
       * Choose connection function: mysql_pconnect/mysql_connect
       */
      if ($this->persistent)
        {
          $func = 'mysql_pconnect'; 
        }
      else
        {
          $func = 'mysql_connect'; 
        }

      /**
       * Try to connect to MySQL server
       */
      $this->conn = @$func($this->host, $this->user, $this->password);
      if (!$this->conn)
        { 
          return false; 
        }
      /**
       * use database
       */
      if (@!mysql_select_db($this->database, $this->conn)) 
        { 
          return false; 
        }
      
      return true;
    }
	
  /**
   * try to disconnect from MySQL server
   */
  function disconnect()
    {
      return (@mysql_close($this->conn));
    }

  /**
   * retrun error message
   */
  function error()
    {
      return (mysql_error());
    }
  
  /**
   * execute query $sql
   * @param string query to execute
   */
  function execQuery($sql)
    {
      $this->_queries[] = $sql;
      $this->result = @mysql_query($sql, $this->conn);
      $this->lastQuery = $sql;
      return ($this->result != false);
    }
  function secureItem( $item )
  {
    return mysql_real_escape_string( $item );
  }
  
  /**
   * returns number of affected rows
   * @return integer number of affected rows
   */
  function affectedRows()
    {
      return (@mysql_affected_rows($this->conn));
    }

  /**
   * returns number of rows in query result
   * @return integer number of rows, returned from query
   */
  function numRows()
    {
      return (@mysql_num_rows($this->result));
    }
  
  /**
   * retruns Last inserted ID
   * @return integer last inserted ID
   */
  function insertedId()
    {
      return (mysql_insert_id($this->conn));
    }

  /**
   * get query results as object
   * @return integer result as object 
   */
  function fetchObject()
    {
      return (@mysql_fetch_object($this->result, MYSQL_ASSOC));
    }
  
  /**
   * get query results as assoc array
   * @return assoc array on success false - on fail
   */
  function fetchAssoc()
    {
       return (@mysql_fetch_assoc($this->result));
    }

  /**
   * @return array result as array
   */
  function getArray()
    {	
      $res = array();
      while($row = $this->fetchAssoc())
        {
          $res[] = $row;
        }
      return $res;
    }
  
  /**
   * free results
   * @return int 0 - on fail, else - on success
   */
  function freeResult()
    {
      return (@mysql_free_result($this->result));
    }

  /**
   * TODO
   */
  function renameDb($newName)
    {

    }
  /**
   * TODO
   */
  function addColumn($tableName, $columnName)
    {
      
    }
  /**
   * TODO
   */
  function dropColumn($tableName, $columnName)
    {

    }
  /**
   * TODO
   */
  function renameColumn($tableName, $oldName, $newName)
    {
      
    }
  /**
   * Метод переименовывает таблицу в текущей, выбранной базе данных
   * @param string $oldName старое название таблицы
   * @param string $newName новое имя таблицы
   * @return 
   */
  function renameTable($oldName, $newName)
    {
      /**
       * @todo как обрабатывать текст запроса
       */
      $sql = "ALTER TABLE ".$oldName." RENAME TO ".$newName;
    }

  /**
   * TODO
   */
  function createDump($dumpFile, $dumpFormat, $dbCharset, $useDropTables, $quoteNames, $completeInsert, $extendedInsert, $noDatas)
    {

    }

  /**
   * TODO
   */
  function importDump($dumpFile, $dumpFormat)
    {

    }

  function generate( $object ) {
    $instance = new db( "", "", "", "" );// :-)
    
    $array = $object->toArray();
    $table_name = $array[ 'table' ];
    //dBug::tdump( $array );
    $keys_info = "";
    $fields = array();
    foreach( $array[ 'fields' ] as $field => $field_info ) {
      $field_type = ( !$field_info[ 'params' ][ 'size' ] )? $field_info[ 'type' ] : $field_info[ 'type' ] . '(' . $field_info[ 'params' ][ 'size' ] . ')';

      $not_null = ( !$field_info[ 'params' ][ 'not_null' ] )? '' : ' NOT NULL';
      
      $auto_increment = ( !$field_info[ 'params' ][ 'auto_increment' ] )? '' : ' AUTO_INCREMENT';

      $field_row = sprintf( 
            '   %s %s%s%s',
            $field,
            $field_type,
            $not_null,
            $auto_increment
      );
      $fields[] = $field_row;
    }
    $primary_key = $array[ 'primary' ];
    $table = sprintf( "
CREATE TABLE %s (
%s,
 PRIMARY KEY (%s)
);\n",
        $table_name, 
        implode( $fields, ",\n" ),
        $primary_key
    );

    echo $table;
    
    if( sizeof( $array[ 'many' ] ) ) {
      foreach( $array[ 'many' ] as $relation => $relation_info ) {
        $relation_table = sprintf( "
CREATE TABLE %s (
    %s,
    %s
);\n",
        $relation_info[ 'relation_table' ],
        $relation_info[ 'local' ] . ' INT(11) NOT NULL',
        $relation_info[ 'foreign' ] . ' INT(11) NOT NULL',
        $primary_key
       );
        echo $relation_table;
      } // foreach
    }
  }

}//db
?>