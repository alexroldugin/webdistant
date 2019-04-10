<?php

require_once( 'entitycollection.class.php' );

  /**
   * Класс реализующий управление связями типа "многие ко многим"
   *
   * $Id: entityrelations.class.php 11 2009-01-12 23:20:51Z alxs $
   *
   * @author Alex Roldugin <alex.roldugin@gmail.com>
   */
class EntityRelations extends EntityCollection {
  /**
   * @var string тип модели у объекта владельца-данными этой коллекции
   */
  protected $_ownerModel = "";

  /**
   * @var string имя таблицы пересечения для хранения связей
   */
  protected $_relationTable = "";

  protected $_owner;

  /**
   * Конструктор.
   *
   * @param string $owner_model название типа модели объекта-владельца
   * @param string $collection_model название типа модели у объектов коллекции
   * @param string $relation_table имя таблицы пересечения, для хранения связей
   */
  public function __construct( $owner, $collection_model, $relation_table ) {
    parent::__construct( $collection_model );

    if( !$owner ) {
      throw new Exception( 'Onwer didn`t specify' );
    }

    $owner_model = get_class( $owner );
    $models_list = Registry::getEntry( 'models' );

    if( !array_key_exists( $owner_model, $models_list ) ) {
      throw new Exception( 'Unknown model specified: ' . $owner_model );
    }
    if( !$relation_table ) {
      throw new Exception( 'Relation table didn`t specify' );
    }
    
    $this->_relationTable = $relation_table;
    $this->_owner = $owner;
  }
  
  /**
   * Метод создания связей объекта-владельца с подчиненными объектами
   */
  public function link() {
    if( !$this->_owner->getPrimaryKeyValue() ) {
      throw new Exception( 'Owner have to have numeric id' );
    }

    $to_link = $this->getNonlinkedIds();
    if( !sizeof( $to_link ) ) { return; }
    
    $query = $this->getLinkItemsQuery( $to_link );
    
    $sql = $this->getSqlDriver();
    $result = $sql->execQuery( $query );
    
    if( !$result ) {
      throw new Exception( 'Query error: ' . $sql->error() );
    }

  } // link

  /**
   * Метод удаления связи объекта-владельца с подчиненными объектами
   */
  public function unlink() {
    if( !$this->_owner->getPrimaryKeyValue() ) {
      throw new Exception( 'Owner have to have numeric id' );
    }

    if( !sizeof( $this->_collection ) ) { return; }
    $to_unlink = array();
    foreach( $this->_collection as $item )  {
      $id = $item->getPrimaryKeyValue();
      if( !$id ) { throw new Exception( 'Attemption to save relation with wrong id combination' ); }
      $to_unlink[] = $id;
    }

    $query = $this->getUnLinkItemsQuery( $to_unlink );

    $sql = $this->getSqlDriver();
    $result = $sql->execQuery( $query );
    
    if( !$result ) {
      throw new Exception( 'Query error: ' . $sql->error() );
    }
    
    
  } // unlink

  public function unlinkByIds() {
  } // unlinkByIds

  protected function getSqlDriver() {

    if( !class_exists( 'Registry' ) ) {
      throw new Exception( 'Unknown Class Registry' );
    }
    $sql = Registry::getEntry( 'sql' );
    if( !$sql ) {
      throw new Exception( 'Sql Driver not found' );
    }

    return $sql;
  } // getSqlDriver

  
  /**
   * Метод возвращаюий id записей для которых еще не существует связи
   *
   * @todo сделать нормальную реализацию
   *
   * @return array массив хранящий id записей без связи
   */
  protected function getNonlinkedIds() {
    $items_ids = array();
    if( sizeof( $this->_collection ) ) {
      foreach( $this->_collection as $item )  {
        $id = $item->getPrimaryKeyValue();
        if( !$id ) { throw new Exception( 'Attemption to save relation with wrong id combination' ); }
        $items_ids[] = $id;
      }
      $non_linked = array();
      if( !sizeof( $items_ids ) ) { return $non_linked; }
      
      $query = $this->getLoadExistentRelationsQuery( $items_ids );
      $sql = $this->getSqlDriver();
      
      $result = $sql->execQuery( $query );
    
      if( !$result ) {
        throw new Exception( 'Query error: ' . $sql->error() );
      }

      $rows = $sql->getArray();
      $nonlinked_ids = array();
      
      $collection_model = $this->_model;    
      $collection_obj = new $collection_model();
      $collection_pk = $collection_obj->getPrimaryKeyColumn();
      $existent_ids = array();
      
      if( sizeof( $rows ) ) {
        foreach( $rows as $row ) {
          $row_id = $row[ $collection_pk ];
          $existent_ids[] = $row_id;
        }
      }
      
      $nonlinked_ids = array();
      foreach( $items_ids as $id ) {
        if( !in_array( $id, $existent_ids ) ) {
          $nonlinked_ids[] = $id;
        }
      }

      return $nonlinked_ids;
    }
    
  } // getUnlinkedIds

  /**
   * Метод выполняет сохранение данных списка и 
   * сохранение связей с данными этого списка
   */
  public function save() {
    if( !$this->_owner->getPrimaryKeyValue() ) {
      throw new Exception( 'Owner have to have numeric id' );
    }
    parent::save();
    $this->link();
  } // save
  
  // -------------------- Queries --------------------
  /**
   * Метод генерирует запрос на извлечение существующих
   * связей из тех, что перечислены в коллекции
   *
   * @param array $ids записей со стороны "многие" ( ids подчиненных объектов )
   */
  protected function getLoadExistentRelationsQuery( $ids ) {
    $owner_model = $this->_ownerModel;
    $collection_model = $this->_model;
    
    $collection_obj = new $collection_model();

    $collection_pk = $collection_obj->getPrimaryKeyColumn();
    $owner_pk = $this->_owner->getPrimaryKeyColumn();
    
    $query = sprintf(
          "SELECT %s FROM %s WHERE %s='%d' AND %s IN(%s)",
          $collection_pk,
          $this->_relationTable,
          $owner_pk,
          $this->_owner->getPrimaryKeyValue(),
          $collection_pk,
          implode( $ids, ', ' )          
    );

    return $query;
  }

  /**
   * Метод генерирующий запрос на добавление связей между
   * объектом-владельцем и подчиненными объектами
   *
   * @param array id элементов, которые нужно связать с данным объектом
   */
  protected function getLinkItemsQuery( $ids ) {
    $owner_model = $this->_ownerModel;
    $collection_model = $this->_model;
    
    $collection_obj = new $collection_model();

    $collection_pk = $collection_obj->getPrimaryKeyColumn();
    $owner_pk = $this->_owner->getPrimaryKeyColumn();

    $id_pairs = array();
    if( sizeof( $ids ) ) {
      foreach( $ids as $cur_id ) {
        
        $id_pairs[] = sprintf( "( '%d', '%d' )",
                               $this->_owner->getPrimaryKeyValue(),
                               $cur_id
                      );
      }
    }

    $query = sprintf(
      'INSERT INTO %s ( %s, %s ) VALUES %s',
      $this->_relationTable,
      $owner_pk, $collection_pk,
      implode( $id_pairs, ', ' )
    );
    //dBug::dump( $id_pairs );
    return $query;
  }
  /**
   * Метод генерирующий запрос на удаления связей между объектом-владельцем
   * и подчиненными объектами ( id которых указаны в $ids )
   *
   * @param array $ids id связных объектов, связи с которыми необходмо удалить
   */
  protected function getUnLinkItemsQuery( $ids ) {
    $owner_model = $this->_ownerModel;
    $collection_model = $this->_model;
    
    $collection_obj = new $collection_model();

    $collection_pk = $collection_obj->getPrimaryKeyColumn();
    $owner_pk = $this->_owner->getPrimaryKeyColumn();

    $query = sprintf(
               "DELETE FROM %s WHERE %s='%d' AND %s IN ( %s )",
               $this->_relationTable,
               $owner_pk,
               $this->_owner->getPrimaryKeyValue(),
               $collection_pk,
               implode( $ids, ', ')
    );
    return $query;
  }
}
?>