<?php

require_once __DIR__ . '/vendor/autoload.php';

$files = scandir($tmp = sys_get_temp_dir(), null);

foreach ($files as $file)
{
    if (preg_match('~\.bxfs$~', $file)) {
        \Bavix\Helpers\File::remove($tmp . '/' . $file);
    }
}
