<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *
 * Name:     Authentic
 * Author:          Matthew Machuga
 * Location:        https://github.com/machuga/authentic/
 * Created:         April 26, 2011
 * Requirements:    PHP5.3+, CodeIgniter 2.0+, PHPActiveRecord nightly build
 * Description:     Simple authentication library that uses the PHPActiveRecord 
 *                  ORM.  Allows for various meta tables, easily extendible.
 *
 */

class User extends ActiveRecord\Model {
    static $before_save = array('encrypt_password');

    public static function authenticate($identity, $password, $return_user = false)
    {
        $user = static::find_user($identity);
        if ($user && $user->password === static::hash_password($password, $user->salt))
        {
            return ($return_user) ? $user : true;
        }
        else 
        {
         return ($return_user) ? null : false;
        }
    }

    protected static function identity_type($identity)
    {
        if (is_numeric($identity))
        {
            return 'id';
        }
        else
        {
            return (strpos($identity, '@') !== false) ? 'email' : 'username';
        }
    }

    public static function find_user($identity, $allow_id = false)
    {
        $type = 'find_by_'.static::identity_type($identity);
        return ($type === 'find_by_id' && ! $allow_id) ? false : static::$type($identity);
    }


    // Note: Will likely switch to SHA256 w/ hash()
    protected function generate_salt($new = false)
    {
        if ( ! $this->salt || $new)
        {
            $this->salt = sha1('-'.time().'-');  
        }
    }

    protected function hash_password($password, $salt)
    {
        return sha1("-{$password}-{$salt}-");
    }

    /**
     * 
     * Callbacks
     *
     */

    public function encrypt_password() 
    {
        if ($this->password) 
        {
            $this->generate_salt();
            $this->password = $this->hash_password($this->password, $this->salt);
        }
    }
}

/* End of file User.php */
/* Location: ./application/models/User.php */
