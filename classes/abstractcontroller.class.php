<?php

  /**
   * @author Alex Roldugin <alex.roldugin@gmail.com>
   */
abstract class AbstractController {
  
  /**
   * @var mixed отвечает за работу с данными в формате JSON
   */
  protected $_json = null;

  /**
   * @var mixed объект типа Page, представляющий
   * собой результат работы приложения - страница
   */
  protected $_view = null;

  /**
   * @var mixed объект класса Topnav реализующий навигационную панель страницы,
   * располагаемой в верхнее ее части.
   */
  protected $_topNav = null;
  
  protected function _getSqlLog() {
    $_CONFIG = Registry::getEntry( 'config' );

    $show_log = $_CONFIG[ 'debug' ][ 'debug.show_sqllog' ];
    
    $sql_log = "";
    if( $show_log ) {
      require_once( 'db/sqlmode.lib.php' );
      $lighter = new SqlMode();
      $sql =  Registry::getEntry( 'sql');
      
      $sql_log = '<li id="sqllog__">' . $sql->log( $lighter ) . '</li>';
    }

    return $sql_log;
  }
  
  /**
   * Метод создания навигационной панели, расположенной в верху страницы.
   */
  protected function _getNavBar() {
    if( $this->_topNav ) { return $this->_topNav; }
    
    require_once( 'view/topnav.class.php' );
    $navBar = new TopNav();

    $this->_topNav = $navBar;
    return $navBar;
  }
  
  public function __construct() {
    $this->_flashMessageKey = '__flashMessages';
    
    $this->_view = new Page();
    
    $topNav = $this->_getNavBar();
    $this->_topNav = $topNav;
    
    $this->_view->addData( '__topNav', $topNav );    
    $this->_view->addJs( '/js/jquery/jquery.js' );
    $this->_view->addJs( '/js/jquery/jquery-ui.js' );
    $this->_view->addJs( '/js/jquery/jquery.form.js' );
    $this->_view->addJs( '/js/wymeditor/jquery.wymeditor.pack.js' );

    $this->_view->addJs( '/js/general.js' );

    $this->_view->addCss( '/css/jquery/ui.theme.css' );

    if( $this->isAjaxRequest() ) {
      $this->_json = new Services_JSON();
      $this->_view->setUseLayout( false );
    }

    $this->processFlashMessages();
  }
  
  protected function processFlashMessages() {
    $this->_flashMessageKey = '__flash_messages';
    if( array_key_exists( $this->_flashMessageKey, $_SESSION ) && sizeof( $_SESSION[ $this->_flashMessageKey ] ) ) {
      foreach( $_SESSION[ $this->_flashMessageKey ] as $message ) {
        $this->_view->addMessage( $message );
      }
    }
    $_SESSION[ $this->_flashMessageKey ] = array();
  }

  /**
   * Метод добавления шаблона файла в информационную часть страницы
   */
  public function addContent( $template ) {
    $_CONFIG = Registry::getEntry( 'config' );
    $inner_tpl = $_CONFIG[ 'general.path' ][ 'path.root' ]  . 'templates/' . $template;
    
    return $this->_view->addContent( $inner_tpl );
  }
  /**
   * Метод который вызывается до начала работы 'action'-метода
   */
  public function preActivate() {
    
  }
  
  /**
   * Метод который вызывается после окончания выполнения работы 'action'-метода
   */
  public function postActivate() {
    $_CONFIG = Registry::getEntry( 'config' );
    
    $script = 'layout/layout.tpl.php';
    $layout_tpl = $_CONFIG[ 'general.path' ][ 'path.root' ] . $_CONFIG[ 'general.path' ][ 'path.application' ] . $script;
    
    $this->_view->addData( '__log', $this->_getSqlLog() );
    $this->_view->setLayoutTemplate( $layout_tpl );
    if( !$this->isAjaxRequest() ) {
      $this->_view->publish();
    }
  }
  
  protected function isAjaxRequest() {
    return ( array_key_exists( 'HTTP_X_REQUESTED_WITH', $_SERVER ) &&
                     $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] == 'XMLHttpRequest'
            );
  }
  protected function isPost() {
    return $_SERVER[ 'REQUEST_METHOD' ] == 'POST';
  }

  /**
   * Метод возвращает значение переданное методом POST
   *
   * @param string $name имя поля
   * @param mixed $default значение по умолчанию
   */
  protected function getPost( $name, $default = '' ) {
    $result = !empty( $_POST[ $name ] )? $_POST[ $name ] : $default;
    return $result;
  }
  protected function saveFlashMessage( $message ) {
    $_SESSION[ $this->_flashMessageKey ][] = $message;
  }

} // AbstractController
?>