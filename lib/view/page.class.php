<?php
  /**
   * Page class
   * 
   * @author Alex Roldugin <alex.roldugin@gmail.com>
   */

class Page {
  /**
   * @var string $_template шаблон информационной части
   */
  protected $_infoTemplate = "";

  protected $_infoTemplatePath = "";

  /**
   * @var string $_layoutTemplate базовый шаблон
   */
  protected $_layoutTemplate = "";

  /**
   * @var string $_title page title
   */
  protected $_title = "Noname page";

  /**
   * @var array $_mData данные для страницы
   */
  protected $_mData = array();

  /**
   * @var array $_mCss набор Css скриптов
   */
  protected $_mCss = array();

  /**
   * @var array $_mJs Javascript
   */
  protected $_mJs = array();

  /**
   * @var array $_mErrors хранит сообщения об ошибках
   * возникших во время работы.
   */
  protected $_mErrors = array();

  /**
   * @var array $_mMessages хранит сообщения, которые необходимо отобразить
   * на странице
   */
  protected $_mMessages = array();
  

  /**
   * @var bool $_useLayout признак, нужно ли использовать базовый шаблон
   */
  protected $_useLayout = true;

  protected $_infoTemplateContent = "";
    
  /**
   * Метод установки информационного шаблона
   *
   * @param string $template путь к файлу шаблона
   */
  function setLayoutTemplate( $template ) {
    if( !is_readable( $template ) || !is_file( $template ) ) {
      throw new Exception( 'Couldn`t find layout template: ' . $template );
    }
    $this->_layoutTemplate = $template;
  }

  /**
   * Set type of page construction
   *
   * @param bool $status true - page consists of one template,
   * false - page consists of top, bottom and main parts.
   */
  public function setUseLayout( $status ) {
    $this->_useLayout = $status;
  }

  public function setTitle($title) {
    $this->_title = $title;
  }

  public function getTitle() {
    return $this->_title;
  }

  public function addData($item_name, $data_item) {
    if( !$item_name ) {
      return false;
    }
    
    $this->_mData[ $item_name ] = $data_item;
  } // addData

  public function addCss( $url ) {
    $this->_mCss[] = $url;
  }
  public function addJs( $url ) {
    $this->_mJs[] = $url;
  }
  public function addError( $error ) {
    $this->_mErrors[] = $error;
  }
  public function addMessage( $message ) {
    $this->_mMessages[] = $message;
  }

  /**
   * Метод загрузки содержимого файла в виде строки
   */
  protected function loadFile( $filename ) {
    ob_start();
    include( $filename );
    $result = ob_get_contents();
    ob_end_clean();
    
    return $result;
  }

  function addContent( $filename ) {
    if( !is_readable( $filename ) || !is_file( $filename ) ) {
      throw new Exception( 'Couldn`t find template: ' . $filename );
    }
    $content = $this->loadFile( $filename );
    $this->_infoTemplateContent .= $content;
    
    return $content;
  }
  
  function publish() {
    $content = $this->_infoTemplateContent;
    if( $this->_useLayout ) {

      $this->addData( '__layoutContent', $this->_infoTemplateContent );
      $content = $this->loadFile( $this->_layoutTemplate );
    }
        
    echo( $content );
  } // publish

  function publishJson( $json_data ) {
    echo( $json_data );
  }

  /**
   * Method generates copyright years.
   * If $begin < $curYear then Format: $begin -- $curYear
   * else Format: $curYear
   *
   * @param $beginYear start year
   */
  function getCopyrightYears($beginYear) {
    $curYear = date("Y");
    if($beginYear < $curYear) {
      return $beginYear . "&nbsp;&mdash;&nbsp;" . $curYear;
    } else {
      return $curYear;
    }
  }

  /**
   * Возвращает отформатированные в виде списка сообщения об ошибках
   */
  function getErrorsBlock() {
    $result = '';
    if( $this->_mErrors ) {
      $result .= '<ul>';
      foreach( $this->_mErrors as $error ) {
        $result .= "<li>" . $error . "</li>\n";
      }
      $result .= '</ul>';
    }
    return $result;
  }

  function getMessagesBlock() {
    $result = '';
    if( $this->_mMessages ) {
      $result .= '<ul>';
      foreach( $this->_mMessages as $message ) {
        $result .= "<li>" . $message . "</li>\n";
      }
      $result .= '</ul>';
    }
    return $result;
  }

  /**
   * Replace special symbols to html-entities
   */
  function html($code) {
    return htmlspecialchars($code, ENT_QUOTES);
  }
} // class
?>