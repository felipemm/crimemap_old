<?php
/**
* Ajax Login Module v1.1
*
* Ajax Login Module is a nice Php-AJAX Login used to authenticate users without reloading a login page.
* Easy to integrate with your existing php applications with no further configuration and coding.
*
*
* @copyright     Copyright 2009, Christopher M. Natan
* @link          http://phpstring.co.cc/phpclasses/modules/ajax-login-module/
* @version       $Revision$
* @modifiedby    $LastChangedBy$
* @lastmodified  $Date$
* @email         chris.natan@gmail.com
*
* Dual licensed under the MIT and GPL licenses.
* Redistributions of files must retain the above copyright notice.
*/

/*
* Main Configuration
*/
error_reporting(0);
define('MYSQL_HOSTNAME', 'mysql.felipematos.com');  /* hostname */
define('MYSQL_USERNAME', 'fmm_dbadmin');       /* username */
define('MYSQL_PASSWORD', 'fmoreira1986');   /* password */
define('MYSQL_DATABASE', 'fmm_crimemap'); /* database */
define('SUCCESS_LOGIN_GOTO', '../index.php'); /* If login successful then it will redirect to */
define('USERS_TABLE_NAME', 'crimemap_user');/* if the defined table in USERS_TABLE_NAME doesn't exist in the Database, this module  will attempt to create. */
define('CITY_ID', 1); //define default city of the system

//message strings
$msg[0] = "Conexão com o banco falhou!";
$msg[1] = "Não foi possível selecionar o banco de dados!";

/* Advance Configuration - no need to edit this section */
define('AJAX_TIMEOUT',        '10000000');
define('AJAX_TARGET_ELEMENT', 'ajax_target');
define('AJAX_WAIT_TEXT',      'Please wait...');
define('AJAX_FORM_ELEMENT',   'ajax_form');
define('AJAX_WAIT_ELEMENT',   'ajax_wait');
define('AJAX_NOTIFY_ELEMENT', 'ajax_notify');

?>