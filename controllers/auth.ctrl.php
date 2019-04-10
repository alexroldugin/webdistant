<?php
require_once( 'abstractcontroller.class.php' );

class AuthController extends AbstractController {
  public function logoutAction() {
    $this->_view->setTitle( 'Glossary page' );
    $this->_topNav->setActive( 'logout' );
  }

}

?>