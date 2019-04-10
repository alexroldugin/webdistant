<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <title><?= $this->getTitle(); ?></title>
    <meta name="keywords" content=""/>
    <meta name="description" content=""/>
    <link rel="stylesheet" type="text/css" href="/css/style.css"/>
    <!--[if lt IE 7]><link rel="stylesheet" type="text/css" href="/css/ie.css"/><![endif]-->

   <?
       if( sizeof( $this->_mJs ) ) :
          foreach( $this->_mJs as $script ) :
             echo( sprintf( "<script type='text/javascript' src='%s'></script>\n", $script ) );
          endforeach;
       endif;
   ?>
   <?
       if( sizeof( $this->_mCss ) ) :
          foreach( $this->_mCss as $script ) :
             echo( sprintf( "<link rel='stylesheet' href='%s' type='text/css' />\n", $script ) );
          endforeach;
       endif;
   ?>
  </head>
  <body>    
      <ul id="wrap">
         <li id="topmenu">
           <?= $this->_mData[ '__topNav' ]->publish(); ?>
         </li>
         <li id="body">
             <?= $this->_mData[ '__layoutContent' ]; ?>
         </li>
         <li id="footer">
            &copy;<?= $this->getCopyrightYears( 2009 ); ?> Михайлова Татьяна Васильевна
         </li>
            <?= $this->_mData[ '__log' ]; ?>
      </ul>    
  </body>
</html>
