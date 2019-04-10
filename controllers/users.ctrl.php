<?php
require_once( 'abstractcontroller.class.php' );

class UsersController extends AbstractController {
  public function indexAction() {
    $this->_view->setTitle( 'Users` page' );
    $this->_topNav->setActive( 'users' );
  }

}

?>