<?php

require_once( 'registry.class.php' );
require_once( 'changable.class.php' );
require_once( 'entityrelations.class.php' );

  /**
   * Класс описывает работу сущностями любого вида ( объекты с любым набором полей и вложенных объектов ),
   * является отображением объектов предметной области на реляционную структуру.
   *
   * $Id: activerecord.class.php 11 2009-01-12 23:20:51Z alxs $
   * @author Alex Roldugin <alex.roldugin@gmail.com>
   */
abstract class ActiveRecord extends Changable {
  /**
   * @var array ассоциативный массив в котором хранятся поля записи
   */
  protected $_fields = array();

  /**
   * @var array массив хранит связи типа "многие ко многим"
   */
  protected $_many = array();

  protected $_one = array();

  /**
   * @var string имя поля первичного ключа
   */
  protected $_primaryKey = null;

  /**
   * @var string название таблицы
   */
  protected $_tableName = null;

  public function __construct() {
    $this->setUp();
    $this->updateCrc();
    $this->markNew();
  }

  /**
   * Метод вызываемый для создания структуры записи ( т.е. в нем объявляются
   * все поля, название таблицы, и т.д. )
   */
  protected function setUp() {
    if( $this->getPrimaryKeyColumn() === null ) {
      $this->hasColumn( 'id', 'INT', array( 'primary' => true,
                                            'size' => 11,
                                            'auto_increment' => true,
                                            'not_null' => true,
                                           ) );
    }
    if( !$this->getTableName() ) {
      $this->setTableName( get_class( $this ) );
    }
  }

  /**
   * Метод возвращающий представление объкта в виде массива
   */
  public function toArray() {
    $values = array();
    foreach( $this->_fields as $field => $field_info ) {
      $values[ $field ] = $field_info[ 'value' ];
    }
    return array(
       'values'  => $values,
       'fields'  => $this->_fields,
       'many'    => $this->_many,
       'primary' => $this->getPrimaryKeyColumn(),
       'table'   => $this->getTableName(),
       'changed' => $this->isChanged(),
    );
  }

  /**
   * Метод вычисления контрольной суммы данных объекта.
   *
   * @return int контрольная сумма данных объекта.
   */
  protected function calcCrc() {
    $string = "";
    foreach( $this->_fields as $field ) {
      $string .= $field[ 'value' ];
    }

    return crc32( $string );    
  }
  
  /**
   * Метод инициализации объекта из массива
   *
   * @param array массив для инициализации объекта вида:
   * <code>
   *   array(
   *      'key' => $value,
   *      'key1' => $value1,
   *      ...
   *   );
   * </code>
   */
  public function fromArray( $record ) {
    foreach( $record as $key => $value ) {
      if( array_key_exists( $key, $this->_fields ) ) {
        $this->setValue( $key, $value );
      }
    }
  } // fromArray
  
  /**
   * Метод объявления поля записи
   *
   * @param string $name название поля.
   * @param string $type тип поля, это должен быть реальный SQL-тип.
   * @param array $params дополнительные параметры, например, 
   * <code>
   *  array(
   *     'primary' => true, // данное поле является ключевым
   *     'default' => 0,    // значение по умолчанию
   *  )
   * </code>
   */
  public function hasColumn( $name, $type, $params = array()) {
    if( array_key_exists( 'primary', $params ) && $params[ 'primary' ]) {
      $this->_primaryKey = $name;
    }
    $this->_fields[ $name ] = array(
         'type'   => $type,
         'params' => $params,
         'value'  => null,
    );
  } // hasColumn

  /**
   * Метод регистрации связи многие ко многим
   *
   * @param string $class_name 
   * В результате работы создается запись в массиве _many вида:
   * <code>
   *    array(
   *       'local' => $local_key, // первичный ключ текущего объекта
   *       'foreign' => $foreign_key, // первичный ключ со стороны "многие"
   *       'model' => $class_name,    // тип объекта со стороны "один"
   *       'many_table' => $foreign_obj->getTableName(), // тип объекта со стороны "многие"
   *       'relation_table' => $this->getRelationTableName( $foreign_obj->getTableName() ),  // имя таблицы пересечения для реализации связи - генерируется автоматически
   *       'value' => new EntityRelations( $class_name ), // данные в связи
   * );
   * </code>
   */
  public function hasMany( $class_name, $params ) {
    if( !class_exists( 'Registry' ) ) {
      throw new Exception( 'Unknown Class Registry' );
    }
    $models = Registry::getEntry( 'models' );

    if( !array_key_exists( $class_name, $models ) ) {
      throw new Exception( 'Unknown relation class: ' . $class_name );
    }
    $foreign_obj = new $class_name();
    $local_key = ( !array_key_exists( 'local', $params ) )? $this->getPrimaryKeyColumn() : $params[ 'local' ];
    $foreign_key = $foreign_obj->getPrimaryKeyColumn(); // $params[ 'foreign' ];
    $alias = ( !array_key_exists( 'alias', $params ) )? $class_name : $params[ 'alias' ];

    $this->_many[ $alias ] = array(
         'local' => $local_key, // первичный ключ текущего объекта
         'foreign' => $foreign_key, // первичный ключ со стороны "многие"
         'model' => $class_name,    // тип объекта со стороны "один"
         'many_table' => $foreign_obj->getTableName(), // тип объекта со стороны "многие"
         'relation_table' => $this->getRelationTableName( $foreign_obj->getTableName() ),  // имя таблицы пересечения для реализации связи - генерируется автоматически
         'value' => new EntityRelations( $this, $class_name, $this->getRelationTableName( $foreign_obj->getTableName() ) ), // данные в связи
    );
  }

  /**
   * Метод генерации уникального имени таблицы пересечения
   * для реализации связи многие ко многим
   */
  protected function getRelationTableName( $foreign_table ) {
    return $this->getTableName() . '_' . $foreign_table . '_relation';
  }

  /**
   * Метод, возвращающий информацию о поле
   * @return array информация о записи в формате:
   * <code>
   * array(
   *  'type'   => $type, // string // SQL-тип поля
   *  'params' => $params, // array // ассоциативный массив дополнительных параметров поля
   *  'value'  => $value, // mixed // значение поля
   * )
   * </code>
   */
  public function getColumnInfo( $name ) {
    $result = null;
    if( array_key_exists( $name, $this->_fields ) ) {
      $result = $this->_fields[ $name ];
    }
    return $result;
  } // getColumn

  /**
   * Метод установления значения поля записи
   *
   * @param string $name название поля
   * @param string $value значение поля
   */
  public function setValue( $name, $value ) {
    if( !array_key_exists( $name, $this->_fields ) ) {
      throw new Exception( 'Key [' . $name . '] doesn`t exists' );
    }

    $this->_fields[ $name ][ 'value' ] = $value;
  } // setValue

  public function getValue( $name ) {
    $result = null;
    if( array_key_exists( $name, $this->_fields ) ) {
       $result = $this->getFieldValue( $name );
    } else if( array_key_exists( $name, $this->_many ) ) {
       $result = $this->getOneToManyRelation( $name );
    } else {
      throw new Exception( 'Key [' . $name . '] doesn`t exists' );
    }
    return $result;
  } // getValue

  protected function getFieldValue( $name ) {
    $result = $this->_fields[ $name ][ 'value' ];
    return $result;
  }

  /**
   * Метод аналогичен методу getValue только для извлечения
   * списка вложенных объектов.
   * Данные загружаются в поле _many[ $name ][ 'value' ]
   *
   * @param string $name имя поля, для которого необходимо
   * загрузить данные
   */
  public function getOneToManyRelation( $name ) {
    if( !$this->getTableName() ) {
      throw new Exception( 'TableName didn`t specify' );
    }

    $query = $this->getSelectOneToManyRelationQuery( $name );

    $sql = $this->getSqlDriver();

    $result = $sql->execQuery( $query );
    
    if( !$result ) {
      throw new Exception( 'Query error: ' . $sql->error() );
    }

    $rows = $sql->getArray();
    $relations = array();
    $relation_info = $this->_many[ $name ];
    $model = $relation_info[ 'model' ];
    $this->_many[ $name ][ 'value' ]->clear();
    $this->_many[ $name ][ 'value' ]->fromArray( $rows );

    return $this->_many[ $name ][ 'value' ];
  } // getOneToManyRelation

  public function getCollection( $name ) {
    $result = null;
    if( array_key_exists( $name, $this->_many ) ) {
      $result = $this->_many[ $name ][ 'value' ];
    }
    return $result;
  } // getCollection
  
  /**
   * Метод установления названия таблицы БД.
   */
  public function setTableName( $name ) {
    $this->_tableName = $name;
  }

  /**
   * 
   */
  public function getTableName() {
    return $this->_tableName;
  }
  
  public function getPrimaryKeyColumnInfo() {
    return $this->getColumnInfo( $this->_primaryKey );
  }
  public function getPrimaryKeyColumn() {
    return $this->_primaryKey;
  }
  
  public function getPrimaryKeyValue() {
    return $this->getValue( $this->getPrimaryKeyColumn() );
  }

  public function setPrimaryKeyValue( $value ) {
    $this->setValue( $this->getPrimaryKeyColumn(), (int)$value );
  }

  public function getSqlDriver() {

    if( !class_exists( 'Registry' ) ) {
      throw new Exception( 'Unknown Class Registry' );
    }
    $sql = Registry::getEntry( 'sql' );
    if( !$sql ) {
      throw new Exception( 'Sql Driver not found' );
    }

    return $sql;
  } // getSqlDriver
  
  public function update() {
    if( !$this->getTableName() ) {
      throw new Exception( 'TableName didn`t specify' );
    }

    $query = $this->getUpdateQuery();
    $sql = $this->getSqlDriver();

    $result = $sql->execQuery( $query );
    
    if( !$result ) {
      throw new Exception( 'Query error: ' . $sql->error() );
    }
    
    return true;
  } // update

  
  public function insert() {
    if( !$this->getTableName() ) {
      throw new Exception( 'TableName didn`t specify' );
    }

    $query = $this->getInsertQuery();
    $sql = $this->getSqlDriver();

    $result = $sql->execQuery( $query );
    
    if( !$result ) {
      throw new Exception( 'Query error: ' . $sql->error() );
    }
    $this->setPrimaryKeyValue( $sql->insertedId() );
    
    return true;
  } // insert

  /**
   * Метод заполенения полей объекта по id,
   * для работы этого метода должно быть заполнено
   * значение ключевого поля.
   */
  public function fill() {
    if( !$this->getPrimaryKeyValue() ) {
      throw new Exception( 'Primary key value didn`t specify' );
    }

    if( !$this->getTableName() ) {
      throw new Exception( 'TableName didn`t specify' );
    }

    $query = $this->getSelectQuery();
    $sql = $this->getSqlDriver();

    $result = $sql->execQuery( $query );
    
    if( !$result ) {
      throw new Exception( 'Query error: ' . $sql->error() );
    }

    if( $sql->numRows() == 0 ) {
      return false;
    }

    $row = $sql->fetchAssoc();

    $this->fromArray( $row );
    $this->updateCrc();
    $this->markLoaded();
    
    return true;
  }

  /**
   * Метод сохранения записи в БД.
   * Если установлен id записи, то выполняется обновление существующей,
   * иначе создается новая
   */
  public function save() {
    if( !$this->isChanged() ) { return; }
    
    $primary_key_value = $this->getPrimaryKeyValue();

    if( !$primary_key_value ) {
      $this->insert();
    } else {
      $this->update();
    }
    $this->updateCrc();
    //$this->markLoaded();
  }

  /**
   * Метод извлечения имен неключевых полей
   */
  public function extractKeys() {
    $keys = array();
    foreach( $this->_fields as $field_name => $fiel_info ) {
      if( $field_name != $this->getPrimaryKeyColumn() ) {
        $keys[] = $field_name;
      }
    }
    return $keys;
  }

  /**
   * Метод извлечения значений неключевых полей
   *
   * @return array массив неключевых полей вида:
   * <code>
   *   array(
   *     'field1' => $value1, // поле => значение поля
   *     'field2' => $value2,
   *   );
   * </code>
   */
  public function extractValues() {
    $values = array();
    $keys = $this->extractKeys();
    
    if( sizeof( $keys ) ) {
      foreach( $keys as $key ) {
        $values[ $key ] = $this->_fields[ $key ][ 'value' ];
      }
    }
    return $values;
  }

  /**
   * Метод извлечения экланированных значений неключевых полей
   */
  public function secureValues() {
    $values = $this->extractValues();

    foreach( $values as $key => $value ) {
      $values[ $key ] = "'" . $this->secureItem( $value ) . "'";
    }
    return $values;
  }
  // -------------------- Quoting --------------------
  /**
   *
   */
  public function secureItem( $item ) {
    $item = $this->quoteSmart( $item );
    return $item;
  }

  /**
   * Метод, используемый для экранирование значения
   * переменной
   */
  public function quoteString( $value ) {
    $sql = Registry::getEntry( 'sql' );
    $value = $sql->secureItem( $value );
    //$value = addslashes( $value );//mysql_real_escape_string( $value );
    return $value;
  }

  /**
   * Метод, для экранирования переменных.
   * Этот метод учитывает текущие установки magic_quotes.
   * Для экранирования строк используется метод quoteString
   *
   * @param string/int переменная, значение которой нужно проэкранировать
   */
  public function quoteSmart($value) {
    // если magic_quotes_gpc включена - используем stripslashes
    if ( get_magic_quotes_gpc() ) {
      $value = stripslashes( $value );
    }
    // Если переменная - число, то экранировать её не нужно
    // если нет - то окружем её кавычками, и экранируем
    if ( !is_numeric( $value ) ) {
      $value = $this->quoteString( $value );
    }
    return $value;
  } // quoteSmart
  
 
  // ---------------------- Queries -------------------------------
  public function getInsertQuery() {
    $keys = $this->extractKeys();
    $column_keys = implode( $keys, ', ' );
    
    $values = $this->secureValues();
    $column_values = implode( $values, ', ' );

    $query_tpl = "INSERT INTO %s ( %s ) VALUES ( %s )";
    $query = sprintf( $query_tpl,
             $this->getTableName(),
             $column_keys,
             $column_values
    );
    return $query;
  }
  
  public function getUpdateQuery() {
    $values = $this->secureValues();
    
    $key_values = array();
    $primary_key_value = "";
    foreach( $values as $key => $value ) {
      $key_value = $key . ' = ' . $value;

      if( $key != $this->getPrimaryKeyColumn() ) {
        $key_values[] = $key_value;
      } else {
        $primary_key_value = $key_value;
      }
    }
    $primary_key = $this->getPrimaryKeyColumn();
    $primary_key_value = $this->_fields[ $primary_key ][ 'value' ];

    $column_values = implode( $key_values, ', ' );

    $query_tpl = "UPDATE %s SET %s WHERE %s='%d'";
    $query = sprintf( 
                  $query_tpl,
                  $this->getTableName(),
                  $column_values,
                  $primary_key,
                  $this->secureItem( $primary_key_value )
    );
    return $query;
  }

  public function getSelectQuery() {
    $columns = $this->extractKeys();
    $columns[] = $this->getPrimaryKeyColumn();
    
    $query = sprintf(
        "SELECT %s FROM %s WHERE %s='%d'",
        implode( $columns, ', ' ),
        $this->getTableName(),
        $this->getPrimaryKeyColumn(),
        $this->getPrimaryKeyValue()
    );
    
    return $query;
  }

  public function getSelectOneToManyRelationQuery( $name ) {
    $relation_info = $this->_many[ $name ];
    $rel_model = $relation_info[ 'model' ];
    $rel_object = new $rel_model();
    $columns = $rel_object->extractKeys();
    $columns[] = $relation_info[ 'many_table' ] . '.' . $relation_info[ 'foreign' ];
    
    $join_on = $relation_info[ 'many_table' ] . '.' . $relation_info[ 'foreign' ] .
      '=' . $relation_info[ 'relation_table' ] . '.' . $relation_info[ 'foreign' ];

    $query = sprintf(
      "
SELECT %s
FROM %s INNER JOIN %s
ON %s
WHERE %s='%d'
",
      implode( $columns, ', ' ),
      $rel_object->getTableName(),
      $relation_info[ 'relation_table' ],
      $join_on,
      $relation_info[ 'relation_table' ] . '.' . $relation_info[ 'local' ],
      $this->getPrimaryKeyValue()
    );

    return $query;
  }

} // ActiveRecord

?>