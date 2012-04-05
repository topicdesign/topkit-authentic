<?php

namespace Authentic;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * User
 *
 * @package     Authentic
 * @subpackage  Models
 * @category    Authentication
 * @author      Topic Deisgn
 * @link        https://github.com/topicdesign/codeigniter-authentic-authentication
 * @license     http://creativecommons.org/licenses/BSD/
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

class User extends \ActiveRecord\Model {

    # explicit table name  
    //static $table_name = 'users';

    # explicit pk 
    //static $primary_key = 'id';

    # explicit connection name 
    //static $connection = 'default';

    # explicit database name 
    //static $db = '';

    static $before_validation_on_create = array('generate_username');
    
    // --------------------------------------------------------------------
    // Associations
    // --------------------------------------------------------------------

    static $has_many = array(
        array('nonces', 'class_name' => 'Authentic\Nonce')
    );

    // --------------------------------------------------------------------
    // Validations
    // --------------------------------------------------------------------

    static $validates_presence_of = array(
        array('email'),
        array('username'),
        array('password')
    );
    
    // --------------------------------------------------------------------
    
    static $validates_uniqueness_of = array(
        array('email'),
        array('username')
    );
    
    // --------------------------------------------------------------------

    static $validates_length_of = array(
        array('email', 'maximum' => 120),
        array('username', 'maximum' => 60)
    );

    /**
     * custom validation
     *
     * @access  public 
     * @param   void
     *
     * @return  void
     **/
    public function validate()
    {
        if ( ! filter_var($this->email, FILTER_VALIDATE_EMAIL))
        {
            $this->errors->add('email', "must be a valid email address");
        }
    }

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
        if ( ! $salt)
        {
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
     * @param   mixed   $identity   (string) users.username
     *                              (string) users.email
     * @param   string  $password   unencrypted password
     * @param   bool    $return     switch return value
     *
     * @return  mixed   bool        (default)
     *                  object      ActiveRecord user object
     **/
    public static function authenticate($identity, $password, $return = FALSE)
    {
        $user = static::find_user($identity, FALSE);

        if ( ! $user)
        {
            return ($return) ? NULL : FALSE;
        }

        $hash = static::hash_value($password, $user->salt);

        if ($user->password !== $hash)
        {
            return ($return) ? NULL : FALSE;
        }

        return ($return) ? $user : TRUE;
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
     * @return  object  ActiveRecord $user object
     **/
    public static function find_user($identity, $allow_id = TRUE)
    {
        $type = 'find_by_'.static::identity_type($identity);
        if ($type === 'find_by_id' && ! $allow_id)
        {
            return FALSE;
        }
        return self::$type($identity);
    }

    // --------------------------------------------------------------------
    // Private/Protected Methods
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
    //protected function hash_value($value, $salt = NULL)
    public function hash_value($value, $salt = NULL)
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

    public function generate_username()
    {
        if ($this->username) 
        {
            die('here');
            return TRUE;
        }
        $username = array_shift(explode('@',$this->email));
        $options = array(
            'conditions' => array("username LIKE '$username%'")
        );
        $result = self::all($options);
        $names = array();
        foreach ($result as $user)
        {   
            $names[] = $user->username; 
        }
        $i = 1;
        $try = $username;
        while (array_search($try,$names) > -1)
        {
            $try = sprintf('%s%02s',$username,$i);
            $i++;
        }
        $this->username = $try;
    }

    // --------------------------------------------------------------------

}
/**
 * SQL for table

CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(120) DEFAULT NULL,
  `username` varchar(60) DEFAULT NULL,
  `password` char(64) DEFAULT NULL,
  `salt` char(64) DEFAULT NULL,
  `active` tinyint(1) DEFAULT '0',
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

**/
/* End of file User.php */
/* Location: ./models/User.php */
