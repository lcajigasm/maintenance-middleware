# Maintenance Middleware

[![Latest Stable Version](https://poser.pugx.org/luisinder/maintenance-middleware/v/stable)](https://packagist.org/packages/luisinder/maintenance-middleware)
[![Total Downloads](https://poser.pugx.org/luisinder/maintenance-middleware/downloads)](https://packagist.org/packages/luisinder/maintenance-middleware)

### Synopsis

Slim 3 middleware that returns an error when maintenance mode is activated.

### Installation

With Composer:

```sh
composer require luisinder/maintenance-middleware
```

### Params

* Status: Enable/Disable Middleware (global maintenance mode)
* Object returned: This object is return into response when status = true
* Specific pages (optional): Array of specific pages/routes to put in maintenance mode

### Use

#### Global maintenance mode (original behavior)

```php
$errorObject = new Example\ErrorClass();
$app->add(new Luisinder\Middleware\Maintenance(false, $errorObject));
```

#### Specific pages maintenance mode

```php
$errorObject = new Example\ErrorClass();

// Single page
$app->add(new Luisinder\Middleware\Maintenance(false, $errorObject, '/admin'));

// Multiple specific pages
$specificPages = ['/admin', '/dashboard', '/api/admin'];
$app->add(new Luisinder\Middleware\Maintenance(false, $errorObject, $specificPages));

// Using wildcards for pattern matching
$specificPages = ['/admin/*', '/api/admin/*'];
$app->add(new Luisinder\Middleware\Maintenance(false, $errorObject, $specificPages));
```

#### Examples of specific page patterns

- `/admin` - Exact match for /admin page
- `/admin/*` - All pages starting with /admin/ (like /admin/users, /admin/settings)
- `/api/v1/*` - All API v1 endpoints
- `/` - Home page only