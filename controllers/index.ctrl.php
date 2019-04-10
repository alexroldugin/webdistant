<?php
require_once( 'abstractcontroller.class.php' );

class IndexController extends AbstractController {
  public function indexAction() {
    $this->_view->setTitle( ' Strona Główna' );
    $section = new Section();

//     $section->setPrimaryKeyValue( 1 );
//     $section->fill();
//     $array = $section->toArray();
    
//     foreach( range( 0, 10 ) as $index ) {
//       $material = new Material();
//       $material->setPrimaryKeyValue( $index + 1 );
//       //$material->setValue( 'name', 'Тема №' . $index );
//       if($material->fill()) {
//         $section->getCollection( 'materials' )->add( $material );
//       }
//     }
//     //$values = $section->getValue( 'materials' )->toArray();
//     //dBug::dump( $values );
    
//     $section->getCollection( 'materials' )->save();
// //     dBug::Dump( $section->getCollection( 'materials' )->toArray() );
//     //$collection
//     //$collection->save();
//     //$section->getCollection( 'materials' )->unlink();
    $this->_view->addJs( '/js/section.js' );
    if( $this->isAjaxRequest() ) {
      $this->addContent( 'index/viewresponse.tpl.php' ); 
    } else {
      $this->addContent( 'index/index.tpl.php' );
    }
    
  }
  
}

?>