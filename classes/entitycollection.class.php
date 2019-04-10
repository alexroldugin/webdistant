<?php

require_once( 'changable.class.php' );

/**
 * Класс для работы со списками объектов
 *
 * @author Alex Roldugin <alex.roldugin@gmail.com>
 */
class EntityCollection extends Changable {
  /**
   * @var array массив хранящий данные коллекции
   */
  protected $_collection = array();

  /**
   * @var string название модели с объектами типа которой коллекция будит работать.
   */
  protected $_model = "";

  protected function calcCrc() {
    $value = "";
    if( sizeof( $this->_collection )) {
      foreach( $this->_collection as $collection_item ) {
        $value .= $collection_item->isChanged();
      }
    }
    return crc32( $value );
  }

  /**
   * Конструктор.
   *
   * @param string $model модель на основе которой будит строится коллекция
   * ( т.е. будит коллекция состоящая только из объектов типа $model ).
   * Модель $model должна присутствовать в списке используемых в системе моделей.
   */
  public function __construct( $model ) {
    if( !$model ) {
      throw new Exception( 'Model didn`t specify' );
    }
    $models_list = Registry::getEntry( 'models' );

    if( !array_key_exists( $model, $models_list ) ) {
      throw new Exception( 'Unknown model specified: ' . $model );
    }
    
    $this->_model = $model;
  }

  /**
   * Метод представления объекта в виде массива
   */
  public function toArray() {
    $result = array(
         'items' => array(),
         'model' => $this->_model,
         'changed' => $this->isChanged(),
    );
    if( sizeof( $this->_collection ) ) {
      foreach( $this->_collection as $collection_item ) {
        $result[ 'items' ][] = $collection_item->toArray();
      }
    }
    return $result;
  } // toArray

  /**
   * Метод заполнения коллекции записями из массива строк записей ( такой же,
   * какой получается в результате выборки ).
   *
   * @param array $rows массив записей
   */
  public function fromArray( $rows ) {
    $model = $this->_model;
    foreach( $rows as $row ) {
      $relation_object = new $model();
      $relation_object->fromArray( $row );
      $relation_object->updateCrc();
      $relation_object->markLoaded();
      $this->add( $relation_object );
    }
    $this->updateCrc();
    $this->markLoaded();
  } // fromArray

  /**
   * Метод сохранения элементов коллекции
   */
  public function save() {
    if( !$this->isChanged() ) {
      return;
    }
    if( sizeof( $this->_collection ) ) {
      foreach( $this->_collection as $collection_item ) {
        $collection_item->save();
      }
    }
    $this->updateCrc();
    $this->markLoaded();
  } // save

  /**
   * Метод очистки коллекции
   */
  public function clear() {
    $this->_collection = array();
  } // clear

  /**
   * Метод добавляения элемента в коллекцию
   *
   * @param mixed $value элемент, помещаемый в коллецию
   */
  public function add( $value ) {
    if( get_class( $value ) != $this->_model ) {
      throw new Exception( 'Wrong object specified. Collection contains only' . $this->_model . '  not ' . get_class( $value ) );
    }
    
    $this->_collection[] = $value;
  } // add
  
  /**
   * Метод предоставляющий возможность работы с коллекцией напрямую.
   */
  public function &getCollection() {
    return $this->_collection;
  } // getCollection
  
}

?>