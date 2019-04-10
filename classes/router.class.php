<?php

class Router {
  protected $_routes = array();
  public function addRoute( $name, $pattern, $include, $controller, $action, $params = array() ) {
    $this->_routes[ $name ] = array(
         'pattern' => $pattern,
         'include' => $include,
         'controller' => $controller,
         'action' => $action,
         'params' => $params,
    );
  } // addRoute

  protected function _nameParams( $params, $values ) {
    $named_maches = array();
    foreach( $values as $key => $param_value ) {
      if( array_key_exists( $key - 1, $params ) ) {
        $named_maches[ $params[ $key - 1 ] ] = $param_value;
      }
    }
    return $named_maches;
  }
  
  public function run( $uri ) {

    if( sizeof( $this->_routes ) ) {
      foreach( $this->_routes as $route ) {
        
        if( preg_match( $route[ 'pattern' ], $uri, $matches ) ) {
          unset( $matches[ 0 ] );
          $named_maches = $this->_nameParams( $route[ 'params' ], $matches );

          require_once( $route[ 'include' ] );
          $controller = $route[ 'controller' ];
          $action     = $route[ 'action' ];

          $controller_obj = new $controller();
          if( !in_array( $action, get_class_methods( $controller_obj ) ) ) {
            throw new Exception( 'Unkonwn controller [' . $controller. '] action [' . $action . ']' );
          }

          $controller_obj->preActivate();
          $controller_obj->$action( $named_maches );
          $controller_obj->postActivate();
          return;
        }

      }
    }

    throw new Exception( 'Unknown uri controller: uri - [' . $uri . '] ' );
  } // run
} // Router

?>