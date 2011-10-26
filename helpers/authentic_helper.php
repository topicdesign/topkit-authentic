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
        if ( ! isset($CI->app)) {
            $CI->app = new stdClass;
        }
        if ( ! isset($CI->app->user)) {
            $CI->load->library('authentic');
            $CI->app->user = $CI->authentic->current_user();
        }
        return $CI->app->user;
	}
}

// ------------------------------------------------------------------------

/* End of file authentic_helper.php */
/* Location: ./helpers/authentic_helper.php */
