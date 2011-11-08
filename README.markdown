Authentic Authentication for CodeIgniter
=======================================

Authentic is an authentication library that provides a persistent user 
to the [CodeIgniter][1] framework. It requires:

* **PHP 5.3.x**
* [PHPActiveRecord][2].

The package automates securely hashing passwords, and provides an interface 
to *nonce* records used authenticate visitors via 'remember me' cookies or 
activation codes that can be passed in plain text.

[1]: http://codeigniter.com
[2]: http://phpactiverecord.com


Usage
-----

Once logged in, you can use the helper method to get an instance of the 
current user. This function returns an instance of the PHPActiveRecord 
object, or *FALSE* if the current session is not authenticated; 

    if ($user = get_user()) {
        echo $user->email;
    }

### Log In/Out

The login method accepts *email* or *username* and a *password*.

    // log in with email
    $this->authentic->login('foo@example.com', 'password');

    // log in with username
    $this->authentic->login('foo', 'password');

    // an optional third parameter allows you to set a long-life
    //  'remember me' cookie
    $this->authentic->login('foo', 'password', TRUE);
    
    // you can also get the user object returned instead of BOOL
    $user = $this->authentic->login('foo', 'password', NULL, TRUE);

You can determine if the current user is logged in. You can set a config
option to attempt to auto-login.

    $this->authentic->logged_in();

    // you can override the config auto-login option by passing TRUE/FALSE
    $this->authentic->logged_in(FALSE);

Logging out clears your user session, and removes session cookies. You can 
set a config option to also delete 'remember me' cookies.

    $this->authentic->logout();

    // you can override the config option by passing TRUE/FALSE
    $this->authentic->logout(TRUE);


### Activate/Deactivate

You can get an activation code and set a user to inactive state. Inactive users 
can not login by default (see configuration).

    // identity can be user email, username, or id
    $this->authentic->deactivate($identity);

    // you can optionally request a activation code (default expiration)
    $code = $this->authentic->deactivate($identity, TRUE);

    // you can specify expiration length
    $expire = date_create()->modify('+1 week');
    $code = $this->authentic->deactivate($identity, TRUE, $expire);

You can actiavte a user using a valid (non-expired) code or a user record object.

    $user = get_user();
    $this->authentic->activate($user);

    // when using a code, you can optionally request the user object be returned
    $code = $this->input->post('code');
    $user = $this->authentic->activate($code, TRUE);


### Create Users

TBD

I am not sure if I will include user creation as part of the package, 
or leave that for a per-application implementation. Currently, you can 
simply use the standard ActiveRecord method.

    $attributes = array(
        'email'     => 'foo@example.com',
        'username'  => 'foo',
        'password'  => 'plaintext'
    );
    $user = Authentic\User::create($attributes);


Configuration
-------------

The Authentic library uses the standard CodeIgniter config system,
auto-loading from the config folder, passed as the second parameter 
when loading, or via an **initialize()** method.

    $this->load->library('authentic', $config);

    // -- OR --

    $this->load->library('authentic');
    $this->authentic->initalize($config);


### Configuration options


Should the library attempt to auto-login user when checking if user
is currently logged in?

    $config['allow_auto_login'] = TRUE;

Should the library allow inactive users to login?

    $config['allow_inactive_login'] = FALSE;

How long should 'remember me' cookies work? Accepts string formats
supported by [DateTime::modify()](http://www.php.net/manual/datetime.modify.php).

    $config['cookie_length'] = '+10 days';

Name of the 'remember me' cookie. *Will use CI cookie prefix if set globally.*

    $config['cookie_name'] = 'authenticRemember';

Should calling **logout()** clear the 'remember me' cookie?

    $config['clear_remember'] = TRUE;


Credit
------

This package is based on work started by [Matthew Machuga][3], though 
it will be hardly recognizeable as such soon enough. Mostly I liked his 
[Authority][4] package and wanted to flesh this out so I can start 
working with it more seriously.

[3]:http://github.com/machuga
[4]:https://github.com/machuga/codeigniter-authority-authorization
