<?php
require_once( 'abstractcontroller.class.php' );

class GlossaryController extends AbstractController {
  public function indexAction() {
    $this->_view->setTitle( 'Словарь' );
    $this->_topNav->setActive( 'glossary' );

    $this->_view->addJs( '/js/glossary.js' );

    $sql = Registry::getEntry( 'sql' );

    $sql->execQuery( 'SELECT * FROM `glossary` ORDER BY term' );
    $glossary_plain = $sql->getArray();
    
    $glossary = array();
    if( sizeof( $glossary_plain ) ) {
      foreach( $glossary_plain as $term_item ) {
        $term = new Glossary();

        $term->fromArray( $term_item );
        $term_item = $term->toArray();
        $glossary[] = $term_item[ 'values' ];

      }
    }

    $rus = array( 'а', 'б', 'в', 'г', 'д', 'е', 'ж', 'з', 'и', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'э', 'ю', 'я' );
    $eng = range( 'a', 'z' );
    //$glossary = array_merge( array_values( $rus ), array_values( $eng ) );
    //dBug::dump( $glossary );
    $this->_view->addData( 'rus', $rus );
    $this->_view->addData( 'eng', $eng );
    $this->_view->addData( 'glossary', $glossary );

    $this->addContent( 'glossary/index.tpl.php' );

  }

  public function createAction() {
    if( $this->isAjaxRequest() ) {
        $result = array(
                        'result' => false,
                        'data'   => array(
                            'html' => '',
                        )
        );
        
      if( $this->isPost() ) {
        // form submittion
        $term = trim( $this->getPost( 'term', '' ) );
        $def = trim( $this->getPost( 'definition', '' ) );
        $has_errors = false;
        do {
          if( !$term ) {
            $has_errors = true;
            $this->_view->addError( 'Заполните значение поля "Термин"' );
          }
          if( !$def ) {
            $has_errors = true;
            $this->_view->addError( 'Заполните значение поля "Определение"' );
          }
          if( $has_errors ) {
            break;
          }
          
          $glossary = new Glossary();
          $glossary->setValue( 'term', $term );
          $glossary->setValue( 'definition', $def );
          $glossary->save();
          
          $result[ 'data' ][ 'term' ][ 'id' ] = $glossary->getPrimaryKeyValue();
          $result[ 'data' ][ 'term' ][ 'term' ] = $glossary->getValue( 'term' );
          $result[ 'data' ][ 'term' ][ 'def' ] = $glossary->getValue( 'definition' );
          $result[ 'data' ][ 'term' ][ 'html' ] =
            sprintf( '<li style="margin: 5px 0;">
            <span id="term-%d" style="border-bottom: 1px dashed #666; cursor: pointer; font-weight: bold;">%s</span>
            <div id="term-%d-def" style="display: none; margin: 5px 0;">%s</div>
         </li>',
                $glossary->getPrimaryKeyValue(),
                $glossary->getValue( 'term' ),
                $glossary->getPrimaryKeyValue(),
                $glossary->getValue( 'definition' )
              );
          
          $this->saveFlashMessage( 'Термин успешно сохранен' );

        } while( false );
        if( $has_errors ) {
          $page_tpl = 'glossary/createresponse.tpl.php';
          $result[ 'data' ][ 'html' ] = $this->addContent( $page_tpl );
        }
        $result[ 'result' ] = !$has_errors;
      } else {
        $result[ 'result' ] = true;
        $result[ 'data' ][ 'html' ] = $this->addContent( 'glossary/createresponse.tpl.php' );
      }
      $this->_view->publishJson( $this->_json->encode( $result ) );
    } else {
      throw new Exception( 'Only ajax request available' );
    }
  }

  public function viewAction( $params ) {
    if( $this->isAjaxRequest() ) {
      if( !$params[ 'term_id' ] ) {
        $this->_view->addError( 'Термин не найден' );
      } else {
        $glossary = new Glossary();
        $glossary->setPrimaryKeyValue( $params[ 'term_id' ] );
        $glossary->fill();
      
        $this->_view->addData( 'term', $glossary->getValue( 'term' ) );
        $this->_view->addData( 'definition', $glossary->getValue( 'definition' ) );
        $this->_view->addData( 'term_id', $glossary->getPrimaryKeyValue() );
      }
      
      $result[ 'result' ] = true;
      $result[ 'data' ][ 'html' ] = $this->addContent( 'glossary/viewresponse.tpl.php' );
      
      $this->_view->publishJson( $this->_json->encode( $result ) );
    } else {
      throw new Exception( 'Only ajax request available' );
    }  
  }

}

?>