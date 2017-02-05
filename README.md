#README

## What is Osynapsy?
Osynapsy is a MVC php framework. 

##Installation
It's recommended that you use [Composer](https://getcomposer.org/) to install Osynapsy.

```bash
$ composer require osynapsy.org/osynapsy "^0.2.0"
```

This install osynapsy and all required dependencies. Osynapsy require PHP 5.5.0 or newer.

## Usage

Create an index.php file with the following contents:

```php
<?php
require '../vendor/autoload.php';

$kernel = new Osynapsy\Core\Kernel('../etc/site.xml');

echo $kernel->run();
```

