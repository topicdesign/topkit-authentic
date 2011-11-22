<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Authentic Helpers
 *
 * @package		Authentic
 * @subpackage	Helpers
 * @category	Authentication
 * @author		Topic Design
 * @link		https://github.com/topicdesign/codeigniter-authentic-authentication
 */

// ------------------------------------------------------------------------

/**
 * get an instance of the current user object
 * attempt to instantiate one if needed
 *
 * @access	public
 * @param	void
 *
 * @return	mixed   object  ActiveRecord user object
 *                  bool
 */
if ( ! function_exists('get_user'))
{
	function get_user()
    {
        $CI = get_instance();
        if (function_exists('get_app'))
        {
            $obj = get_app();
        }
        else
        {
            $obj = get_instance();
        }
        
        if ( ! isset($obj->user))
        {
            $CI->load->library('authentic');
            $obj->user = $CI->authentic->current_user();
        }
        return $obj->user;
	}
}

// ------------------------------------------------------------------------

/**
 * check to see if there is a logged in user
 *
 * @access public
 * @param  void
 *
 * @return bool
 **/
if ( ! function_exists('logged_in'))
{
    function logged_in($auto_login = NULL)
    {
        $CI = get_instance();
        $CI->load->library('authentic');
        return $CI->authentic->logged_in($auto_login);
    }
}

/* End of file authentic_helper.php */
/* Location: ./helpers/authentic_helper.php */
