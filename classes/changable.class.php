<?php

define( "OBJECT_NEW", 0 );
define( "OBJECT_LOADED", 1 );
define( "OBJECT_DELETED", 2 );

  /**
   * Класс является базовым классом для всех классов, которым
   * необходима функциональность отслеживания изменения объекта
   *
   * $Id: changable.class.php 9 2009-01-12 15:11:32Z alxs $
   *
   * @author Alex Roldugin <alex.roldugin@gmail.com>
   */
abstract class Changable {
  
  /**
   * @var int $_crc последнее зафиксированное состояние объекта.
   */
  protected $_crc;

  /**
   * @var int $_state текущее состояние объекта. Объект может находиться
   * в следующих состояниях:
   *   <dl>
   *     <dt>OBJECT_NEW</dt>
   *     <dd>Объект только что создан ( по умолчанию ).</dd>
   *     <dt>OBJECT_LOADED</dt>
   *     <dd>Объект загружен</dd>
   *     <dt>OBJECT_DELETED</dt>
   *     <dd>Объект удален</dd>
   *   </dl>
   */
  protected $_state = OBJECT_NEW;

  /**
   * Метод обновления контрольной суммы данных объекта.
   */
  protected function updateCrc() {
    $this->_crc = $this->calcCrc();
  }
  
  /**
   * Метод используемый для определения состояния измененности объекта.
   *
   * @return bool true - объект изменен, false - объект не изменен.
   */
  public function isChanged() {
    return ( $this->_crc != $this->calcCrc() );
  }

  /**
   * Абстрактный метод реализующий вычисление контрольной суммы данных объекта.
   */
  abstract protected function calcCrc();

  /**
   * Методы используемые для установки состояния объекта
   */
  public function markNew() { $this->_state = OBJECT_NEW; }  
  public function markLoaded() { $this->_state = OBJECT_LOADED; }  
  public function markDeleted() { $this->_state = OBJECT_DELETED; }

  /**
   * Методы определяющие состояние объектов
   */
  public function isNew() { return $this->_state == OBJECT_NEW; }
  public function isLoaded() { return $this->_state == OBJECT_LOADED; }
  public function isDeleted() { return $this->_state == OBJECT_DELETED; }
} // Changable
?>