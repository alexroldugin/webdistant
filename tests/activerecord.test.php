<?php
require_once( 'bootstrap.php' );
require_once( 'activerecord.class.php' );

require_once( 'simpletest/autorun.php' );

class TestRecord extends ActiveRecord {
  
}

class ActiveRecordTest extends UnitTestCase {
  protected $_record = null;

  public function __construct() {
    Mock::generate( "db", 'MockedDb');
  }
  
  public function setUp() {
    $this->_record = new TestRecord();
    
    $sql = new MockedDb();
    Registry::setEntry( 'sql', $sql );
  }
  
  public function testHasColumnWithParams() {
    $column_name = '__testColumn';
    $this->assertNull( $this->_record->getColumnInfo( $column_name ) );
    
    $column_type = 'VARCHAR';
    $column_params = array( 
                            'key1' => 'value1',
                            'key2' => 'value2'
                          );
    $this->_record->hasColumn( $column_name, $column_type, $column_params );
    $this->assertEqual( $this->_record->getColumnInfo( $column_name ),
                        array(
                             'type'   => $column_type,
                             'params' => $column_params,
                             'value'  => null,
                        ));
  }

  public function testHasColumnWithoutParams() {
    $column_name = '__testColumn';
    $this->assertNull( $this->_record->getColumnInfo( $column_name ) );
    
    $column_type = 'VARCHAR';
    $this->_record->hasColumn( $column_name, $column_type );
    
    $this->assertEqual( $this->_record->getColumnInfo( $column_name ),
                        array(
                             'type'   => $column_type,
                             'params' => array(),
                             'value'  => null,
                        ));
  }
  
  public function testExtractKeys() {
    foreach( range( 0, 10 ) as $index ) {
      $column_name = '__column' . $index;
      $this->_record->hasColumn( $column_name, 'STRING' );
      $keys[] = $column_name;
    }
    $this->assertEqual(
          $this->_record->extractKeys(),
          $keys
    );
  }

  public function testExtractValues() {
    $keys = array();
    foreach( range( 0, 10 ) as $index ) {
      $column_name = '__column' . $index;
      $column_value = '__column=>value' . $index;
      
      $this->_record->hasColumn( $column_name, 'STRING' );
      $this->_record->setValue( $column_name, $column_value );
      $keys[ $column_name ] = $column_value;
    }

    $this->assertEqual(
          $this->_record->extractValues(),
          $keys
    );    
  }
  
  
  public function testGetInsertQuery() {
    $table_name = '__table_name';
    $columns = array();
    $values = array();
    foreach( range( 0, 10 ) as $index ) {
      $column_name = '__column' . $index;
      $column_type = 'VARCHAR( 124 )';
      $column_value = '__col' . $index . '_value';
    
      $columns[] = $column_name;
      $values[]  = "'" . $column_value . "'";
    
      $this->_record->hasColumn( $column_name, $column_type );

      $this->_record->setValue( $column_name, $column_value );
    }
    
    $this->_record->setTableName( $table_name );
    
    $got_query = $this->_record->getInsertQuery();
    $need_query = sprintf(
         "INSERT INTO %s ( %s ) VALUES ( %s )",
         $table_name,
         implode( $columns, ', ' ),
         implode( $values, ', ' )
    );
    $this->assertEqual( $got_query, $need_query );
  }

  public function testGetUpdateQuery() {
    $table_name = '__table_name';
    $primary_key_value = 1543;
    
    $column_values = array();
    foreach( range( 0, 10 ) as $index ) {
      $column_name = '__column' . $index;
      $column_type = 'VARCHAR( 124 )';
      $column_value = '__col' . $index . '_value';
    
      $this->_record->hasColumn( $column_name, $column_type );
      $this->_record->setValue( $column_name, $column_value );

      $column_values[] = $column_name . ' = ' . "'" . $column_value . "'";
    }
    
    $this->_record->setTableName( $table_name );
    $this->_record->setValue( 'id', $primary_key_value );

    $got_query = $this->_record->getUpdateQuery();
    $need_query = sprintf(
         "UPDATE %s SET %s WHERE id='%d'",
         $table_name,
         implode( $column_values, ', ' ),
         $primary_key_value
    );
    $this->assertEqual( $got_query, $need_query );
  }

  public function testGetSelectByIdQuery() {
    $columns = array();
    $values = array();
    foreach( range( 0, 10 ) as $index ) {
      $column_name = '__column' . $index;
      $column_type = 'VARCHAR( 124 )';
      $column_value = '__col' . $index . '_value';
    
      $columns[] = $column_name;
      $values[]  = "'" . $column_value . "'";
    
      $this->_record->hasColumn( $column_name, $column_type );

      $this->_record->setValue( $column_name, $column_value );
    }
    $keys = $this->_record->extractKeys();
    $keys[] = $this->_record->getPrimaryKeyColumn();
    
    $table_name = '__table_name';
    $this->_record->setTableName( $table_name );

    $primary_key_value = 15234;
    $this->_record->setValue( 'id', $primary_key_value );
    
    $need_query = sprintf(
                     "SELECT %s FROM %s WHERE id='%d'",
                     implode( $keys, ', '),
                     $table_name,
                     $primary_key_value
                  );

    $got_query = $this->_record->getSelectQuery();
    $this->assertEqual( 
                        $need_query,
                        $got_query
                       );
  }

  public function testFillWithEmptyPrimaryKeyValue() {
    try {
      $this->_record->fill();
      $this->fail('No exception thrown');
    } catch( Exception $e ) {
      $this->assertWantedPattern('/primary key value/i', $e->getMessage());
    }
  }

  public function testFillWithQueryError() {
    $sql = Registry::getEntry( 'sql' );
    $sql->setReturnValue( 'execQuery', false );
    $sql->expectCallCount( 'numRows', 0 );
    $this->_record->setPrimaryKeyValue( 15234 );
    $this->_record->setTableName( '__tablename' );
    
    try {
      $this->_record->fill();
      $this->fail( 'No exception thrown' );
    } catch( Exception $e ) {
      $this->assertWantedPattern('/query error/i', $e->getMessage());
    }
  }

  public function testFillWhenZeroRowsFetched() {
    $sql = Registry::getEntry( 'sql' );
    $sql->setReturnValue( 'execQuery', true );
    $sql->setReturnValue( 'numRows', 0 );
    $sql->expectCallCount( 'numRows', 1 );
    $this->_record->setPrimaryKeyValue( 15234 );
    $this->_record->setTableName( '__tablename' );
    
    try {
      $this->assertFalse( $this->_record->fill() );
    } catch( Exception $e ) {
      $this->fail( 'Unexpected exception: ' . $e->getMessage() );
    }
  }

  public function testFillWhenRecordFound() {
    $sql = Registry::getEntry( 'sql' );
    $sql->setReturnValue( 'execQuery', true );
    $sql->setReturnValue( 'numRows', 1 );
    $sql->expectCallCount( 'numRows', 1 );
    $this->_record->setPrimaryKeyValue( 15234 );
    $this->_record->setTableName( '__tablename' );

    $fetched = array();
    foreach( range( 0, 10 ) as $index ) {
      $column_name = 'column' . $index;      
      $fetched[ $column_name ] = 'value' . $index;
      
      $this->_record->hasColumn( $column_name, 'VARCHAR' );
    }    
    
    $sql->setReturnValue( 'fetchAssoc', $fetched );
    
    try {
      $this->assertTrue( $this->_record->fill() );
      foreach( $fetched as $key => $value ) {
        $this->assertEqual( $this->_record->getValue( $key ),
                            $value
                            );
      }
    } catch( Exception $e ) {
      $this->fail( 'Unexpected exception: ' . $e->getMessage() );
    }

  }
}

?>