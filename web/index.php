<?php

define('ROOT_DIR', dirname(__DIR__));

require_once ROOT_DIR . '/vendor/autoload.php';

use Bavix\Kernel\Common;

Common::bind('cfg', function () {
    return new \Bavix\Config\Config(ROOT_DIR . '/etc');
});

Common::bind('root', function () {
    return Common::cfg()->get('default')['root'];
});

Common::bind('var', function () {
    return ROOT_DIR . '/var/views/';
});

Common::bind('init', Bavix\FS\Controller::class);

$kernel = Common::init();
echo $kernel->run();
