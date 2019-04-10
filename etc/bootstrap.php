<?php
$_CONFIG = parse_ini_file( 'config.ini', true );

ini_set( 'display_errors', $_CONFIG[ 'debug' ][ 'debug.display_errors' ] );
error_reporting( $_CONFIG[ 'debug' ][ 'debug.error_reporting' ] );

if( !empty( $_CONFIG[ 'general.path' ] ) ) {
  $root_path = $_CONFIG[ 'general.path' ][ 'path.root' ];
  
  $path = '';
  foreach( $_CONFIG[ 'general.path' ] as $key => $cur_path ) {
    if( $key != 'path.root' ) {
      $path .= $root_path . $cur_path . ';';
    } else {
      $path .= $cur_path . ';';
    }
  }
}

set_include_path( get_include_path() . ';' . $path );

require_once( 'dBug.lib.php' );
require_once( 'registry.class.php' );
require_once( 'router.class.php' );
require_once( 'page.class.php' );

require_once( 'db/mysql.drv.php' );
require_once( 'JSON.php' );

$router = new Router();

$sql = new db( $_CONFIG[ 'database' ][ 'host' ],
               $_CONFIG[ 'database' ][ 'login' ],
               $_CONFIG[ 'database' ][ 'password' ],
               $_CONFIG[ 'database' ][ 'database' ] );

if( !$sql->connect() ) {
  throw new Exception( 'Couldn`t connect to database' );
}
if( !$sql->execQuery( 'SET NAMES utf-8' ) ) {
  //throw new Exception( 'SET NAMES error' );
}

Registry::setEntry( 'sql', $sql );

foreach( $_CONFIG[ 'models' ] as $model => $file ) {
  require_once( $file );
}
Registry::setEntry( 'models', $_CONFIG[ 'models' ] );
Registry::setEntry( 'config', $_CONFIG );
Registry::setEntry( 'router', $router );
?>