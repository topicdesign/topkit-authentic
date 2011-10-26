<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * User
 *
 * @package     Authentic
 * @subpackage  Models
 * @category	Authentication
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

class User extends ActiveRecord\Model {

    # explicit table name  
    //static $table_name = 'users';

    # explicit pk 
    //static $primary_key = 'id';

    # explicit connection name 
    //static $connection = 'default';

    # explicit database name 
    //static $db = '';

    // --------------------------------------------------------------------
    // Associations
    // --------------------------------------------------------------------
    
    // --------------------------------------------------------------------
    // Validations
    // --------------------------------------------------------------------
    
    // --------------------------------------------------------------------
    // Setters/Getters
    // --------------------------------------------------------------------

    /**
     * set password property (encrypt)
     *
     * @access  public
     * @param   string  $plaintext   unencrypted password
     *
     * @return  void
     **/
    public function set_password($plaintext)
    {
        $this->assign_attribute('password', $this->hash_value($plaintext));
    }

    // --------------------------------------------------------------------

    /**
     * get (new) salt
     *
     * @access  public
     * @param   void
     *
     * @return  string  "random" 64 character string
     **/
    public function get_salt()
    {
        $salt = $this->read_attribute('salt');
        if ( ! $salt) {
            $salt = $this->salt = bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM));
        }
        return $salt;
    }

    // --------------------------------------------------------------------
    // Public Methods
    // --------------------------------------------------------------------

    /**
     * try to authenticate user from provided credentials
     *
     * @access  public
     * @param   mixed   $identity   (int) users.id
     *                              (string) users.username
     *                              (string) users.email
     * @param   string  $password   unencrypted password
     * @param   bool    $return     switch return value
     *
     * @return  mixed   bool        (default)
     *                  object      ActiveRecord $user object
     **/
    public static function authenticate($identity, $password, $return = FALSE)
    {
        $user = static::find_user($identity, FALSE);
        if ($user && $user->password === static::hash_value($password, $user->salt))
        {
            return ($return) ? $user : TRUE;
        }
        else 
        {
            return ($return) ? null : FALSE;
        }
    }

    // --------------------------------------------------------------------

    /**
     * alias for standard find methods
     *
     * @access  public
     * @param   mixed   $identity   (int) users.id
     *                              (string) users.username
     *                              (string) users.email
     * @param   bool    $allow_id   allow lookup based on users.id
     *
     * @return  mixed   bool 
     *                  object      ActiveRecord $user object
     **/
    public static function find_user($identity, $allow_id = TRUE)
    {
        $type = 'find_by_'.static::identity_type($identity);
        return ($type === 'find_by_id' && ! $allow_id) ? FALSE : self::$type($identity);
    }


    // --------------------------------------------------------------------
    // Private Methods
    // --------------------------------------------------------------------

    /**
     * encrypt value with user specific salt
     *
     * @access  protected
     * @param   string  $value  unencrypted value
     * @param   string  $salt   salt to use in place of $this->salt
     *
     * @return  string  encrypted 64 character string
     **/
    protected function hash_value($value, $salt = NULL)
    {
        if ( ! $salt)
        {
            $salt = $this->salt;
        }
        if ( ! is_string($value))
        {
            $value = json_encode($value);
        }
        return hash('sha256', $salt . $value);
    }

    // --------------------------------------------------------------------

    /**
     * determine identity type
     *
     * @access  protected
     * @param   mixed   $identity   (int) or (string)
     *
     * @return  string  field name of probable identity type
     **/
    protected static function identity_type($identity)
    {
        if (is_numeric($identity))
        {
            return 'id';
        }
        else
        {
            return (filter_var($identity, FILTER_VALIDATE_EMAIL)) ? 'email' : 'username';
        }
    }

    // --------------------------------------------------------------------

}

/* End of file User.php */
/* Location: ./application/models/User.php */
