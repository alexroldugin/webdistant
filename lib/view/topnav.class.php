<?php

class TopNav extends Page {

  protected $_items = array(
         'index' => array(
              'title' => '<img src="/images/home.gif" />',
              'link'  => '/',
         ),
         'users' => array(
             'title' => 'Пользователи',
             'link'  => '/users/',
         ),
         'sections' => array(
             'title' => 'Разделы',
             'link'  => '/sections/',
         ),
         'glossary' => array(
             'title' => 'Словарь',
             'link'  => '/glossary/'
         ),
         'logout' => array(
             'title' => 'Выход',
             'link'  => '/logout/',
         ),
  );

  protected $_acitveItem;
  
  protected $_useLayout = false;

  public function publish() {
    $content = "";
    if( sizeof( $this->_items ) ) {
      $first = true;

      foreach( array_reverse( $this->_items ) as $key => $item ) {
        $class = '';
        $classes = array();
        $item_title = $item[ 'title' ];
        
        if( $first ) {
          $first = !$first;
          $classes[] = 'first';
        }

        if( $this->_acitveItem == $key ) {
          $classes[] = 'active';
        } else {
          $item_title = '<a href="' . $this->html( $item[ 'link' ] ) . '">' . $item_title . '</a>';
        }
        
        if( sizeof( $classes ) ) {
          $class = implode( $classes, ' ' );
          $class = 'class="' . $class . '"';
        }

        $content .= sprintf( 
            '<li %s>%s</li>',
            $class,
            $item_title
        );
      }
      $content = '<ul>' . $content . '</ul>';
    }
    echo( $content );
  }

  public function setActive( $item ) {
    if( !array_key_exists( $item, $this->_items ) ) {
      throw new Exception( 'Unknown topNav bar item: ' . $item );
    }
    $this->_acitveItem = $item;
  }
} // Topnav
?>