<?php

namespace Authentic;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Nonce
 *
 * @package     Authentic
 * @subpackage  Models
 * @category    Authentication
 * @author      Topic Deisgn
 * @link        https://github.com/topicdesign/codeigniter-authentic-authentication
 */

class Nonce extends \ActiveRecord\Model {

    # explicit table name
    static $table_name = 'nonces';

    # explicit pk
    static $primary_key = 'code';

    # explicit connection name
    //static $connection = 'default';

    # explicit database name
    //static $db = '';

    static $before_validation_on_create = array('generate_code');

    // --------------------------------------------------------------------
    // Associations
    // --------------------------------------------------------------------

    static $belongs_to = array(
        array('user', 'readonly' => true, 'class_name' => 'Authentic\User')
    );

    // --------------------------------------------------------------------
    // Validations
    // --------------------------------------------------------------------

    static $validates_uniqueness_of = array(
        array('code')
    );

    // --------------------------------------------------------------------

    static $validates_presence_of = array(
        array('code'),
        array('user_id')
    );

    // --------------------------------------------------------------------

    static $validates_length_of = array(
        array('code', 'is' => 32)
    );

    // --------------------------------------------------------------------
    // Public Methods
    // --------------------------------------------------------------------

    /**
     * generate a unique 32 character string
     *
     * @access  public
     * @param   void
     *
     * @return  void
     **/
    public function generate_code()
    {
        $this->code = substr(bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM)),0,32);
        if (static::exists($this->code))
        {
            self::generate_code();
        }
    }

    // --------------------------------------------------------------------
    // Private/Protected Methods
    // --------------------------------------------------------------------

    // --------------------------------------------------------------------
}

/* End of file Nonce.php */
/* Location: ./models/Nonce.php */
