<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Authentic
 *
 * @package     Authentic
 * @subpackage  Libraries
 * @category    Authentication
 * @author      Topic Deisgn
 * @link        https://github.com/topicdesign/codeigniter-authentic-authentication
 */

/**
 * Based on:
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

    protected $allow_auto_login = TRUE;

    // --------------------------------------------------------------------

    /**
     * Constructor
     *
     * @access  public
     * @param   array   config preferences
     *
     * @return  void
     **/
    public function __construct($config = array())
    {
        $this->_ci = get_instance();

        $this->_ci->lang->load('authentic');
        $this->_ci->load->helper('language');
        $this->_ci->load->helper('authentic');

        if (count($config) > 0)
        {
            $this->initialize($config);
        }
    }

    // --------------------------------------------------------------------

    /**
     * Initialize the configuration options
     *
     * @access  public
     * @param   array   config options 
     * @return  void
     */
    public function initialize($config = array())
    {
        foreach ($config as $key => $val)
        {
            if (method_exists($this, 'set_'.$key))
            {
                $this->{'set_'.$key}($val);
            }
            else if (isset($this->$key))
            {
                $this->$key = $val;
            }
        }
    }

    // --------------------------------------------------------------------

    /**
     * attempt to login with provided credentials
     *
     * @access  public
     * @param   string  $identity   users.username or users.email
     * @param   string  $password   unencrypted password
     * @param   bool    $remember   switch setting auto-login
     * @param   bool    $return     switch return value
     *
     * @return  mixed   bool        (default)
     *                  object      ActiveRecord $user object
     */
    public function login($identity, $password, $remember = FALSE, $return = FALSE)
    {
        $user = Authentic\User::authenticate($identity, $password, TRUE);
        if ($user)
        {
            $this->_ci->session->set_userdata('user_id', $user->id);
            if ($remember)
            {
                // Set remember_code and new cookie
            }
            $this->add_message(lang('logged_in'));
            return ($return) ? $user : TRUE; 
        }
        else
        {
            $this->add_error(lang('invalid_credentials'));
            return ($return) ? null : FALSE;
        }
    }

    // --------------------------------------------------------------------

    /**
     * attempt to auto_login
     *
     * @access  public
     * @param   void
     *
     * @return  bool
     **/
    public function auto_login()
    {
        // todo
        return TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * logout user and clear session data
     *
     * @access  public
     * @param   void
     *
     * @return  bool
     */
    public function logout()
    {
        if ($this->_ci->session->userdata('user_id'))
        {
            $this->_ci->session->unset_userdata('user_id');
            $this->_current_user_id = null;
            $this->_current_user = null;
            $this->add_message(lang('logged_out'));
        }

        // Kill remember cookies and other data
        return TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * set user to active status
     *
     * @access  public
     * @param   mixed   $identity   (object) Authentic\User\ActiveRecord\Model
     *                              (int) users.id
     *                              (string) users.username
     *                              (string) users.email
     * @param   string  $identity   users.username or users.email
     * @param   string  $code       users.activation_code
     * @param   bool    $return     switch to return user object
     *
     * @return  mixed   bool        (default)
     *                  object      ActiveRecord $user object
     **/
    public function activate($identity, $code, $return = FALSE)
    {
        if ($identity instanceof ActiveRecord\Model)
        {
            $identity = $identity->id;
        }
        return Authentic\User::activate($identity, $code, $return);
    }

    // --------------------------------------------------------------------

    /**
     * set user to inactive status
     *
     * @access  public
     * @param   mixed   $identity   (object) Authentic\User\ActiveRecord\Model
     *                              (int) users.id
     *                              (string) users.username
     *                              (string) users.email
     *
     * @return  string  32 character activation code
     **/
    public function inactivate($identity)
    {
        if ($identity instanceof ActiveRecord\Model)
        {
            $identity = $identity->id;
        }
        $user = Authentic\User::inactivate($identity, TRUE);

        return ($user) ? $user->activation_code : FALSE;
    }

    // --------------------------------------------------------------------

    /**
     * determine if user is authenticated
     *   auto-login if allowed
     *
     * @access  public
     * @param   bool    $auto_login     allow attempt to auto-login
     *
     * @return  bool
     */
    public function logged_in($auto_login = NULL)
    {
        if ($auto_login === NULL)
        {
            $auto_login = $this->allow_auto_login;
        }

        $logged_in = (bool) $this->current_user_id();
        if ( ! $logged_in && $auto_login)
        {
            return $this->auto_login();
        }
        return $logged_in;
    }

    // --------------------------------------------------------------------
    
    /**
     * get users.id for authenticated user
     *
     * @access  public
     * @param   void
     *
     * @return  mixed   bool
     *                  integer   users.id
     */
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

    // --------------------------------------------------------------------

    /**
     * get ActiveRecord object for authenticated user
     *
     * @access  public
     * @param   void
     *
     * @return  mixed   bool
     *                  object   ActiveRecord $user object
     */
    public function current_user()
    {
        if ( ! $this->_current_user)
        {
            if ($this->current_user_id())
            {
                $this->_current_user = Authentic\User::find_by_id($this->current_user_id());
            }
        }
        return $this->_current_user; 
    }

    // --------------------------------------------------------------------

    /**
     * set an error message 
     *
     * @access  private
     * @param   string  $error  text of error message
     *
     * @return  void
     */
    private function add_error($error)
    {
        if (trim($error) != '')
        {
            $this->_errors[] = $error;
        }
    }

    // --------------------------------------------------------------------

    /**
     * set a message
     *
     * @access  private
     * @param   string  $error  text of message
     *
     * @return  void
     */
    private function add_message($msg)
    {
        if (trim($msg) != '')
        {
            $this->_messages[] = $msg;
        }
    }

    // --------------------------------------------------------------------

    /**
     * get array of error messages
     *
     * @access  public
     * @param   void
     *
     * @return  array  error message strings
     */
    public function get_errors()
    {
        return $this->_errors;
    }

    // --------------------------------------------------------------------

    /**
     * get array of messages
     *
     * @access  public
     * @param   void
     *
     * @return  array   message strings
     */
    public function get_messages()
    {
        return $this->_messages;
    }

    // --------------------------------------------------------------------

}
/* End of file Authority.php */
/* Location: ./application/libraries/Authority.php */
