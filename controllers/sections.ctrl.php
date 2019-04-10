<?php
require_once( 'abstractcontroller.class.php' );

class SectionsController extends AbstractController {
  public function indexAction() {
    $this->_view->setTitle( 'Sections` page' );
    $this->_topNav->setActive( 'sections' );
  }

}

?>