<?php
require_once( 'activerecord.class.php' );

class Material extends ActiveRecord {
  protected function setUp() {
    $this->setTableName( 'materials' );
    $this->hasColumn( 'material_id', 'INT', array( 'size' => 11,
                                               'not_null' => true,
                                               'auto_increment' => true,
                                               'primary' => true
                                               ) );
    
    $this->hasColumn( 'name', 'VARCHAR', array( 'size' => 255 ) );

    parent::setUp();
  }
}
?>