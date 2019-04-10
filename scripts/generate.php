<?php
require_once( '../etc/bootstrap.php' );
// ini_set( 'display_errors', 1 );
error_reporting( E_ERROR );

$models = Registry::getEntry( 'models' );

if( sizeof( $models ) ) {
  foreach( $models as $class => $file ) {
    require_once( $file );
    $object = new $class();
    $sql = db::generate( $object );
    unset( $object );
  }
}

?>