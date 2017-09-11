<?php

include_once __DIR__ . '/vendor/autoload.php';

use Bavix\Kernel\Common;

define('ROOT_DIR', __DIR__);

Common::bind('var', function () {
    return ROOT_DIR . '/var/';
});

Common::bind('cache', Bavix\FS\Cache::class);

$worker = new \Bavix\Gearman\Worker();
$worker->addServer('127.0.0.1', 4730);

$worker->addFunction('size', function (GearmanJob $job) {

    $workload = $job->workload();

    $io   = popen('/usr/bin/du -sk \'' . str_replace('\'', '\\\'', $workload) . '\'', 'r');
    $size = fgets($io, 4096);
    $size = substr($size, 0, strpos($size, "\t")) * 1024;
    pclose($io);

    echo $workload . ' ' . \Bavix\Helpers\Str::fileSize($size) . PHP_EOL;

    Common::cache()->set($workload, $size);

});

while ($worker->work()) {
    continue;
}
