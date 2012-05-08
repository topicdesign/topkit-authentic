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
    protected $allow_inactive_login = FALSE;
    protected $cookie_length = '+10 days';
    protected $cookie_name = 'authenticRemember';
    protected $clear_remember = TRUE;
    protected $deactive_length = '+2 hours';



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
        if ( ! $user)
        {
            $this->add_error(lang('invalid_credentials'));
            return ($return) ? null : FALSE;
        }
        
        if ( ! $user->active && ! $this->allow_inactive_login)
        {
            $this->add_error(lang('inactive_user'));
            return ($return) ? null : FALSE;
        }

        $this->set_session($user, $remember);
        return ($return) ? $user : TRUE; 
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
        // is cookie present and nonce exists
        $code = $this->get_nonce_from_cookie();
        if ( ! $code OR ! Authentic\Nonce::exists($code))
        {
            return FALSE;
        }

        // get nonce and user, delete used nonce
        $nonce = Authentic\Nonce::find($code, array('include'=>array('user')));
        $nonce->delete();

        // is nonce expired
        if ($nonce->expire_at->format('U') < date_create()->format('U'))
        {
            $cookie = array(
                'name'   => $this->cookie_name,
                'value'  => '',
                'expire' => '',
            );
            $this->_ci->input->set_cookie($cookie);

            $this->add_error(lang('expired_cookie'));
            return FALSE;
        }

        if ( ! $nonce->user->active && ! $this->allow_inactive_login)
        {
            $this->add_error(lang('inactive_user'));
            return ($return) ? null : FALSE;
        }

        $this->set_session($nonce->user, TRUE);
        return TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * logout user and clear session data
     *
     * @access  public
     * @param   bool    $clear  override $this->clear_remember
     *
     * @return  bool
     */
    public function logout($clear=NULL)
    {
        if (is_null($clear) || ! is_bool($clear))
        {
            $clear = $this->clear_remember;
        }

        if ($this->_ci->session->userdata('user_id'))
        {
            $this->_ci->session->unset_userdata('user_id');
            $this->_current_user_id = null;
            $this->_current_user = null;
            $this->add_message(lang('logged_out'));
            
            if ($clear)
            {
                // remove stored nonce
                $code = $this->get_nonce_from_cookie();
                if ($code && Authentic\Nonce::exists($code))
                {
                    $nonce = Authentic\Nonce::find($code);
                    $nonce->delete();
                }

                // Kill remember cookies and other data
                $cookie = array(
                    'name'   => $this->cookie_name,
                    'value'  => '',
                    'expire' => '',
                );
                $this->_ci->input->set_cookie($cookie);
            }
        }
        return TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * set user to active status
     *
     * @access  public
     * @param   mixed   $identity   (object) Authentic\User
     *                              (string) Authentic\Nonce.code
     * @param   bool    $return     switch to return user object
     *
     * @return  mixed   bool        (default)
     *                  object      ActiveRecord User object
     **/
    public function activate($identity, $return = FALSE)
    {
        if ( ! is_string($identity) && ! $identity instanceof Authentic\User)
        {
            return FALSE;
        }

        // did we get a nonce code
        if (is_string($identity))
        {
            if ( ! Authentic\Nonce::exists($identity))
            {
                return FALSE;
            }

            // get nonce and user, delete used nonce
            $nonce = Authentic\Nonce::find($identity,
                array('include'=>array('user'))
            );
            $nonce->delete();

            // is nonce expired
            if ($nonce->expire_at->format('U') < date_create()->format('U'))
            {
                $this->add_error(lang('expired_nonce'));
                return FALSE;
            }
            // nonce->user is read only, get new instance
            $user = Authentic\User::find($nonce->user->id);
        }
        else
        {
            $user = $identity;
        }

        $user->active = 1;
        if ( ! $user->save())
        {
            // catch error
        }

        return ($return) ? $user : TRUE; 
    }

    // --------------------------------------------------------------------

    /**
     * set user to inactive status
     * (optional) provide a nonce to reactivate
     *
     * @access  public
     * @param   mixed   $user       (object) Authentic\User
     *                              (int) users.id
     *                              (string) users.username
     *                              (string) users.email
     * @param   bool    $code       should we return a nonce object
     * @param   object  $expire     DateTime object to use for nonce.expire_at
     *
     * @return  mixed   bool        (default)
     *                  object      ActiveRecord Nonce object
     **/
    public function deactivate($user, $code=FALSE, $expire=NULL)
    {
        if ( ! $user instanceof Authentic\User)
        {
            $user = Authentic\User::find_user($user, TRUE);
        }

        if ( ! $user)
        {
            return FALSE;
        }

        $user->active = FALSE;
        if ( ! $user->save())
        {
            // catch error
        }

        if ( ! $code)
        {
            return TRUE;
        }

        // create Nonce
        if (is_null($expire) || ! $expire instanceof DateTime)
        {
            $expire = date_create()->modify($this->deactive_length);
        }
        $attributes = array(
            'user_id'   => $user->id,
            'expire_at' => $expire
        );
        return Authentic\Nonce::create($attributes);
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
        if (is_null($auto_login))
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
     * set user session
     *
     * @access  public
     * @param   object  $user       ActiveRecord User object
     * @param   bool    $remember   toogle creating new nonce
     *
     * @return void
     **/
    public function set_session($user, $remember=FALSE)
    {
        $this->_ci->session->set_userdata('user_id', $user->id);
        $this->add_message(lang('logged_in'));
        if ( ! $remember)
        {
            return;
        }

        // create Nonce and save to cookie
        $expire = date_create()->modify($this->cookie_length);
        $attributes = array(
            'user_id'   => $user->id,
            'expire_at' => $expire
        );
        $nonce = Authentic\Nonce::create($attributes);

        $cookie = array(
            'name'   => $this->cookie_name,
            'value'  => $nonce->code,
            'expire' => $expire->format('U') - date_create()->format('U'),
        );
        $this->_ci->input->set_cookie($cookie);
    }

    // --------------------------------------------------------------------

    /**
     * use CI input library to get (prefixed) cookie
     *
     * @access  private
     * @param   void
     *
     * @return string
     **/
    private function get_nonce_from_cookie()
    {
        $cookie = $this->cookie_name;
        if ($prefix = $this->_ci->config->item('cookie_prefix'))
        {
            $cookie = $prefix . $cookie;
        }

        return $this->_ci->input->cookie($cookie);
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
