<?php
spl_autoload_register(
    function($class) {
        $part = explode("\\",$class);
        if (!is_array($part) || $part[0] != 'Osynapsy') {
            return;
        }
        $filename = __DIR__.'/../src/'.implode('/',$part).'.php';
        require $filename;
    },
    true,
    false
);

