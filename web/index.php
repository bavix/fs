<?php

define('ROOT_DIR', dirname(__DIR__));

require_once ROOT_DIR . '/vendor/autoload.php';

use Bavix\Kernel\Common;

Common::bind('debug', function () {

    static $debug;

    if (null === $debug)
    {
        $debug = Common::cfg()->get('default')
            ->getData('debug', false);
    }

    if ($debug)
    {
        $whoops = new \Whoops\Run();
        $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
        $whoops->register();
    }

    return $debug;

});

Common::bind('twig', function () {

    $debug  = Common::debug();
    $loader = new \Twig_Loader_Filesystem(Common::views());
    $twig   = new \Twig_Environment($loader, ['debug' => $debug]);

    if ($debug)
    {
        $twig->addExtension(new \Twig_Extension_Debug());
    }

    return $twig;

});

Common::bind('cfg', function () {
    return new \Bavix\Config\Config(ROOT_DIR . '/etc');
});

Common::bind('root', function () {
    return Common::cfg()->get('default')['root'];
});

Common::bind('var', function () {
    return ROOT_DIR . '/var/';
});

Common::bind('views', function () {
    return Common::var() . 'views/';
});

Common::bind('cache', Bavix\FS\Cache::class);
Common::bind('init', Bavix\FS\Controller::class);

$kernel = Common::init();
echo $kernel->run();
