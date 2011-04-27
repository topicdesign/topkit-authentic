<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *
 * Name:            Authentic
 * Author:          Matthew Machuga
 * Location:        https://github.com/machuga/authentic/
 * Created:         April 26, 2011
 * Requirements:    PHP5.3+, CodeIgniter 2.0+, PHPActiveRecord nightly build
 * Description:     Simple authentication library that uses the PHPActiveRecord 
 *                  ORM.  Allows for various meta tables, easily extendible.
 *
 */

class Authentic {

    protected $_ci = null;
    protected $_current_user_id = null;
    protected $_current_user = null;
    protected $_errors = array();
    protected $_messages = array();

    public function __construct()
    {
        $this->_ci = get_instance();
    }

    public function login($identity, $password, $remember = false, $return_user = false)
    {
        $user = User::authenticate($identity, $password, true);
        if ($user)
        {
            $this->_ci->session->set_userdata('user_id', $user->id);
            if ($remember)
            {
                // Set remember_code and new cookie
            }
            $this->add_message("You've been logged in successfully");
            return ($return_user) ? $user : true; 
        }
        else
        {
            $this->add_error("Invalid username or password");
            return ($return_user) ? null : false;
        }
    }

    public function logout()
    {
        if ($this->_ci->session->userdata('user_id'))
        {
            $this->_ci->session->unset_userdata('user_id');
            $this->_current_user_id = null;
            $this->_current_user = null;
            $this->add_message("You've been logged out successfully");
        }

        // Kill remember cookies and other data
        return true;
    }

    public function logged_in()
    {
        return (bool) $this->current_user_id();
    }

    public function current_user_id()
    {
        if ( ! $this->_current_user_id)
        {
            if ($this->_ci->session->userdata('user_id'))
            {
                $this->_current_user_id = $this->_ci->session->userdata('user_id');
            }
        }
        return $this->_current_user_id;
    }

    public function current_user()
    {
        if ( ! $this->_current_user)
        {
            if ($this->current_user_id())
            {
                $this->_current_user = User::find_by_id($this->current_user_id());
            }
        }
        return $this->_current_user; 
    }

    public function add_error($error)
    {
        if (trim($error) != '')
        {
            $this->_errors[] = $error;
        }
    }
    
    public function add_message($msg)
    {
        if (trim($msg) != '')
        {
            $this->_message[] = $msg;
        }
    }

    public function get_errors()
    {
        return $this->_errors;
    }

    public function get_messages()
    {
        return $this->_messages;
    }


}
/* End of file Authority.php */
/* Location: ./application/libraries/Authority.php */

