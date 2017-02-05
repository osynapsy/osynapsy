# What is Osynapsy?
Osynapsy is a MVC php framework. 

##Installation
It's recommended that you use [Composer](https://getcomposer.org/) to install Osynapsy.

```bash
$ composer require osynapsy.org/osynapsy "^0.2.0"
```

This install osynapsy and all required dependencies. Osynapsy require PHP 5.5.0 or newer.

## Usage
Create a webroot directory and chroot in.

```bash
mkdir webroot

cd webroot
```

create an index.php file with the following contents:

```php
<?php
ob_start();
require_once('../vendor/autoload.php');

$Kernel = new Osynapsy\Core\Kernel('../etc/instanceconfig.xml', filter_input(INPUT_GET,'q'));
echo $Kernel->run();

ob_end_flush();
```

