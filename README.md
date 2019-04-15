# Pikkuleipa

[![Build Status](https://travis-ci.org/DASPRiD/Pikkuleipa.svg?branch=master)](https://travis-ci.org/DASPRiD/Pikkuleipa)
[![Coverage Status](https://coveralls.io/repos/github/DASPRiD/Pikkuleipa/badge.svg?branch=master)](https://coveralls.io/github/DASPRiD/Pikkuleipa?branch=master)
[![Latest Stable Version](https://poser.pugx.org/dasprid/pikkuleipa/v/stable)](https://packagist.org/packages/dasprid/pikkuleipa)
[![Total Downloads](https://poser.pugx.org/dasprid/pikkuleipa/downloads)](https://packagist.org/packages/dasprid/pikkuleipa)
[![License](https://poser.pugx.org/dasprid/pikkuleipa/license)](https://packagist.org/packages/dasprid/pikkuleipa)

Pikkuleipa is a cookie manager for PSR-7 compliant applications, utilizing [JSON Web Tokens](https://jwt.io/) for
security and allowing the handling of multiple independent cookies.

## Installation

Install via composer:

```bash
$ composer require dasprid/pikkuleipa
```

## Getting started (for [Expressive](https://github.com/zendframework/zend-expressive))

### Import the factory config

Create a file named `pikkuleipa.global.php` or similar in your autoloading config directory:

```php
<?php
return (new DASPRiD\Pikkuleipa\ConfigProvider())->__invoke();
```

This will introduce a few factories, namely you can retrieve the following objects through that:

- `DASPRiD\Pikkuleipa\CookieManager` through `DASPRiD\Pikkuleipa\CookieManagerInterface`
- `DASPRiD\Pikkuleipa\TokenManager` through `DASPRiD\Pikkuleipa\TokenManagerInterface`

### Configure Pikkuleipa

For Pikkuleipa to function, it needs a few configuration variables and RSA key pair for signing JWTs. 

1. Copy the file doc/example-config.php into `config/autoload/` directory and adjust the values as needed.

2. Generate RSA key pair:

```
$ ssh-keygen -t rsa -b 4096 -f data/jwtRS256.key
# Don't add passphrase
$ openssl rsa -in data/jwtRS256.key -pubout -outform PEM -out data/jwtRS256.key.pub 
```

3. Add newly created keys to configuration file:

```
<?php
return [
    'pikkuleipa' => [
        //...
        'token' => [
            //…            
            'signature_key' => file_get_contents('data/jwtRS256.key'),
            'verification_key' => file_get_contents('data/jwtRS256.key.pub'),
        ],
    ],
];
```

### Using the cookie manager

The token manager should usually not be of interest to you. The important part is the cookie manager, which you can
either use through the container, if you are using PSR/Container, or by other means. It concretely gives you three
actions you can do, which are setting cookies, getting cookies and expiring cookies.

#### Setting cookies

Setting a cookie is really easy. First you either get an existing cookie from the cookie manager or you create a new
one. Then you set that cookie on a PSR-7 response and return the modified response to the user.

The `setCookie` method takes two additional parameters beside the response and the cookie. The first one is whether the
cookie should expire at the end of the browser session, which defaults to false. The second one defines whether the
`setCookie` call should override a previous `expireCookie` call, which defaults to true.

```php
<?php
use DASPRiD\Pikkuleipa\Cookie;
use DASPRiD\Pikkuleipa\CookieManagerInterface;

$cookieManager = $container->get(CookieManagerInterface::class);
$cookie = new Cookie('foo');
$cookie->set('bar', 'baz');

$newResponse = $cookieManager->setCookie($response, $cookie);
```

#### Getting cookies

Getting cookies is also quite simple. When retrieving a cookie, the cookie- and the token manager will verify that the
cookie exists and its contents are legit. If something fails, a new empty cookie instance is returned.

```php
<?php
use DASPRiD\Pikkuleipa\CookieManagerInterface;

$cookieManager = $container->get(CookieManagerInterface::class);
$cookie = $cookieManager->getCookie($serverRequest, 'foo');

echo $cookie->get('bar'); // Outputs: bar
```

#### Expiring cookies

Expiring cookies is just as simple as setting a cookie. You can either expire a cookie by its instance or by name:

```php
<?php
use DASPRiD\Pikkuleipa\CookieManagerInterface;

$cookieManager = $container->get(CookieManagerInterface::class);
$cookie = $cookieManager->getCookie($serverRequest, 'foo');

$newResponse = $cookieManager->expireCookie($cookie);

// Or:
$newResponse = $cookieManager->expireCookieByName('foo');
```

## About the name

Pikkuleipa is the Finnish word for "cookie" or "biscuit", nothing fancy here!
