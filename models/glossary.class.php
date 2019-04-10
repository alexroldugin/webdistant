<?php
require_once( 'activerecord.class.php' );

class Glossary extends ActiveRecord {
  protected function setUp() {
    $this->setTableName( 'glossary' );
    
    $this->hasColumn( 'term_id', 'INT', array( 'size' => 11,
                                               'not_null' => true,
                                               'auto_increment' => true,
                                               'primary' => true
                                               ) );
    $this->hasColumn( 'term', 'VARCHAR', array( 'size' => 255 ) );
    $this->hasColumn( 'definition', 'VARCHAR', array( 'size' => 1000 ) );
    
    parent::setUp();
  }
}
?>