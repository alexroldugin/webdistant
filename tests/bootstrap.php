<?php
$_CONFIG = parse_ini_file( '../etc/config.ini', true );

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
require_once( 'db/mysql.drv.php' );

?>