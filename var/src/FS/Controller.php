<?php

namespace Bavix\FS;

use Bavix\Exceptions\NotFound\Page;
use Bavix\Helpers\Arr;
use Bavix\Helpers\Dir;
use Bavix\Helpers\File;
use Bavix\Helpers\Str;
use Bavix\Helpers\Stream;
use Bavix\Kernel\Common;

class Controller
{

    /**
     * Controller constructor.
     */
    public function __construct()
    {
        Common::debug();
    }

    /**
     * @return \Twig_Environment
     */
    protected function twig()
    {
        return Common::twig();
    }

    protected function reader($string)
    {
        $string = urldecode($string);

        if (File::isFile($string))
        {
            $ext = pathinfo($string, PATHINFO_EXTENSION);
            $mime = new \Mimey\MimeTypes();

            header('X-Accel-Redirect: /' . basename(Common::root()) . $this->uri(), true);
            header('Content-Type: ' . $mime->getMimeType($ext), true);
            die;
        }

        if (($_GET['download'] ?? null) === 'zip')
        {
            set_time_limit(0);

            $tmp = new Tmp($string);

            if (!File::isFile($tmp))
            {
                shell_exec('cd ' . escapeshellcmd(dirname($string)) . '; zip -r \'' . $tmp . '\' \'' . escapeshellcmd(basename($string)) . '\' -0');
            }

            $filename = str_replace(['"', "'", ' ', ','], '_', basename($string));

            header('X-Accel-Redirect: ' . $tmp, true);
            header('Content-Type: application/zip', true);
            header('Content-Disposition: attachment;filename="' . $filename . '.zip"', true);
            header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 604800) . ' GMT', true);
            die;
        }

        if (Dir::isDir($string))
        {
            $dir = scandir($string, null);
            $dir = Arr::filter($dir, function ($str) {
                return $str{0} !== '.' || $str === '..';
            });

            $files = Arr::filter($dir, function ($str) {
                return File::isFile(Common::root() . '/' . $this->uri() . '/' . $str);
            });

            $folders = Arr::filter($dir, function ($str) {
                return !File::isFile(Common::root() . '/' . $this->uri() . '/' . $str);
            });

            sort($files);
            sort($folders);

            $dir = array_merge($folders, $files);

            return $this->twig()->render('directory.twig', [
                'title'       => 'FS - ' . $this->uri(),
                'breadcrumbs' => explode('/', rtrim(urldecode($this->uri()), '/')),
                'items'       => Arr::map($dir, function ($item) {
                    return new Item(Common::root(), $this->uri(), $item);
                })
            ]);
        }

        throw new Page('Path `' . $string . '` not found');
    }

    protected function uri()
    {
        $uri   = $_SERVER['REQUEST_URI'] ?? '/';
        $query = $_SERVER['QUERY_STRING'] ?? '';

        if (!empty($query))
        {
            return str_replace('?' . $query, '', $uri);
        }

        return $uri;
    }

    public function run()
    {
        $root = Common::root();
        $dir  = $root . $this->uri();

        return $this->reader($dir);
    }

}
