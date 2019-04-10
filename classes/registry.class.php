<?php
class Registry {

  protected $_store = array();

  // Hold an instance of the class
  protected static $_instance;
  
  public function setEntry( $key, $value ) {
    $instance = self::getInstance();
    $instance->_store[ $key ] = $value;
  }

  public function &getEntry( $key ) {
    $instance = self::getInstance();
    $result = null;
    if( array_key_exists( $key, $instance->_store ) ) {
      $result = $instance->_store[ $key ];
    }
    return $result;
  }

  public function getInstance() {
    if ( !isset( self::$_instance ) ) {
      $class = __CLASS__;
      self::$_instance = new $class();
    }
    return self::$_instance;
  }
} // Registry
?>