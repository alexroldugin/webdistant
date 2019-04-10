<?php

if( !class_exists( 'Registry' ) ) {
  throw new Exception( 'Couldn`t find Registry' );
}

$router = Registry::getEntry( 'router' );

// -------------------- Index/Default Controller --------------------
$router->addRoute( 'index', '!^/?$!', 'index.ctrl.php', 'IndexController', 'indexAction' );

// -------------------- Users Controller --------------------
$router->addRoute( 'usersIndex', '!^/users/?$!', 'users.ctrl.php', 'UsersController', 'indexAction' );

// -------------------- Sections Controller --------------------
$router->addRoute( 'sectionsIndex', '!^/sections/?$!', 'sections.ctrl.php', 'SectionsController', 'indexAction' );

// -------------------- Glossary Controller --------------------
$router->addRoute( 'glossaryIndex', '!^/glossary/?$!', 'glossary.ctrl.php', 'GlossaryController', 'indexAction' );
$router->addRoute( 'glossaryCreate', '!^/glossary/create/?$!', 'glossary.ctrl.php', 'GlossaryController', 'createAction' );
$router->addRoute( 'glossaryView', '!^/glossary/view-([0-9]+)/?$!', 'glossary.ctrl.php', 'GlossaryController', 'viewAction', array( 'term_id' ) );

// -------------------- Logout Controller --------------------
$router->addRoute( 'logout', '!^/logout/?$!', 'auth.ctrl.php', 'AuthController', 'logoutAction' );
?>