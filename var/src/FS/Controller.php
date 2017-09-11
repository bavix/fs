<?php

namespace Bavix\FS;

use Bavix\Exceptions\NotFound\Page;
use Bavix\Helpers\Arr;
use Bavix\Helpers\Dir;
use Bavix\Helpers\File;
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

        if (($_GET['download'] ?? null) === 'zip')
        {
            set_time_limit(0);

            $tmp = new Tmp();
            shell_exec('cd ' . escapeshellcmd(dirname($string)) . '; zip -r \'' . $tmp . '\' \'' . escapeshellcmd(basename($string)) . '\' -0');

            header('Content-Disposition: attachment; filename="' . basename($string) . '.zip"');
            header('Content-Type: application/octet-stream');
            header('Content-Description: File Transfer');
            header('Content-Length: ' . filesize($tmp));
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Expires: 0');
            readfile($tmp);
            exit;
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
                'breadcrumbs' => explode('/', rtrim($this->uri(), '/')),
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
