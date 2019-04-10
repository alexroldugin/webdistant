<?php
require_once( 'activerecord.class.php' );

class Section extends ActiveRecord {
  protected function setUp() {
    $this->setTableName( 'sections' );
    $this->hasColumn( 'section_id', 'INT', array( 'size' => 11,
                                               'not_null' => true,
                                               'auto_increment' => true,
                                               'primary' => true
                                               ) );
    
    $this->hasColumn( 'theme', 'VARCHAR', array( 'size' => 255 ) );
    $this->hasMany( 'Material', array(
                                        'local' => 'section_id',
                                        'alias' => 'materials',
                                      )
                   );

    parent::setUp();
  }
}
?>