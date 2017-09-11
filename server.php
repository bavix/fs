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

$client = new \Bavix\Gearman\Client();
$client->addServer('127.0.0.1', 4730);

$worker->addFunction('size', function (GearmanJob $job) use ($client) {

    $workload = $job->workload();

    if (is_link($workload) || preg_match('~/\.\.?/~', $workload) || null !== Common::cache()->get($workload))
    {
        return;
    }

    $client->doLowBackground('scan', $workload);

    $io   = popen('/usr/bin/du -sk \'' . str_replace('\'', '\\\'', $workload) . '\'', 'r');
    $size = fgets($io, 4096);
    $size = substr($size, 0, strpos($size, "\t")) * 1024;
    pclose($io);

    echo $workload . ' ' . \Bavix\Helpers\Str::fileSize($size) . PHP_EOL;

    Common::cache()->set($workload, $size);

});

$worker->addFunction('scan', function (GearmanJob $job) use ($client) {

    $workload = $job->workload();

    if (!\Bavix\Helpers\Dir::isDir($workload))
    {
        return;
    }

    $dirs = scandir($workload, null);
    unset($dirs[0], $dirs[1]);

    foreach ($dirs as $dir)
    {
        if (!\Bavix\Helpers\Dir::isDir(\Bavix\SDK\Path::slash($workload) . $dir))
        {
            continue;
        }

        $client->doLowBackground('size', \Bavix\SDK\Path::slash($workload) . $dir);
    }

});

while ($worker->work())
{
    continue;
}
