Authentic Authentication for CodeIgniter
=======================================

Authentic is an authentication library that provides a persistent user 
to the [CodeIgniter][1] framework. It requires **PHP 5.3.x** and [PHPActiveRecord][2]

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

### Log In

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

### Log Out

Logging out clears your user session, and removes cookies

    $this->authentic->logout();

### Create Users

TBD

### Activate (via email)

TBD

### Reset Password

TBD


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

TBD

Credit
------

This package is based on work started by [Matthew Machuga][3], though 
it will be hardly recognizeable as such soon enough. Mostly I liked his 
[Authority][4] package and wanted to flesh this out so I can start 
working with it more seriously.

[3]:http://github.com/machuga
[4]:https://github.com/machuga/codeigniter-authority-authorization
