<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| Authentic Config
| -------------------------------------------------------------------------
*/

/**
 * Should the library attempt to auto-login user when checking
 * if user is currently logged in
 *
 * @var bool
 **/
$config['allow_auto_login'] = TRUE;

/**
 * time 'remember me' cookie should persist
 *   accepts string formats supported by DateTime::modify()
 *
 * @link http://www.php.net/manual/datetime.modify.php
 *
 * @var string
 **/
$config['cookie_length'] = '+10 days';

/**
 * name of 'remember me' cookie
 *
 * @var string
 **/
$config['cookie_name'] = 'authenticRemember';

/**
 * should calling 'logout' method clear remember code 
 *
 * @var bool
 **/
$config['clear_remember'] = TRUE;


/* End of file authentic.php */
/* Location: ./config/authentic.php */
