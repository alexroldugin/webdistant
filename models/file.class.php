<?php
require_once( 'activerecord.class.php' );

class File extends ActiveRecord {
  protected function setUp() {
    $this->setTableName( 'files' );
    
    $this->hasColumn( 'file_id', 'INT', array( 'size' => 11,
                                               'not_null' => true,
                                               'auto_increment' => true,
                                               'primary' => true
                                               ) );
    $this->hasColumn( 'name', 'VARCHAR', array( 'size' => 255 ) );
    $this->hasColumn( 'size', 'INT', array( 'size' => 11  ) );
    
    parent::setUp();
  }
}
?>