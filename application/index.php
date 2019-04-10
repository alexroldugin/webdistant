<?php
session_start();
require_once( '../etc/bootstrap.php' );
require_once( 'router.php' );
//dBug::Dump( $_SERVER );
//print_r( $_SESSION );
$router = Registry::getEntry( 'router' );
try {
  $router->run( $_SERVER[ 'REQUEST_URI' ] );
} catch( Exception $e ) {
  echo '<b>Exception: </b>' . $e->getMessage();
}
?>