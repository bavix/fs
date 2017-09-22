<?php

namespace Bavix\FS;

use Bavix\Gearman\Client;
use Bavix\Helpers\Dir;
use Bavix\Helpers\File;
use Bavix\Helpers\Str;
use Bavix\Kernel\Common;
use Bavix\SDK\Path;
use Bavix\Slice\Slice;

class Item
{

    protected $stat;

    protected $uri;

    protected $root;
    protected $name;

    public function __construct($root, $uri, $name)
    {
        $this->uri  = Path::slash($uri);
        $this->root = Path::slash($root);
        $this->name = $name;
    }

    protected function stat($type)
    {
        if (!$this->stat)
        {
            $this->stat = @stat($this->path());
        }

        return $this->stat[$type] ?? null;
    }

    protected function path()
    {
        return urldecode($this->root . $this->uri . $this->getName());
    }

    public function getIcon()
    {
        if ($this->getName() === '..' || Dir::isDir($this->path()))
        {
            return 'fa-folder';
        }

        $mime = Common::cfg()->get('mime')->asArray();
        $type = $this->getMimeType();

        if ($type)
        {
            if (isset($mime[$type]))
            {
                return $mime[$type];
            }

            $mime_parts = explode('/', $type, 2);
            $mime_group = $mime_parts[0];

            if (isset($mime[$mime_group]))
            {
                return $mime[$mime_group];
            }
        }

        return 'fa-file-o';
    }

    public function getMimeType()
    {
        if (File::isFile($this->path()))
        {
            return @mime_content_type($this->path());
        }

        return null;
    }

    public function getPath()
    {
        if ($this->getName() === '..' || Dir::isDir($this->path()))
        {
            return $this->uri . $this->name;
        }

        return $this->uri . $this->name;
    }

    public function getFullSize()
    {
        if ($this->getName() !== '..' && Dir::isDir($this->path()))
        {
            $size = Common::cache()
                ->get($this->path());

            if ($size)
            {
                return Str::fileSize($size);
            }

            $client = new Client();
            $client->addServer('127.0.0.1', 4730);

            $client->doLowBackground('size', $this->path());
        }

        return false;
    }
    
    protected $_fileSize;
    protected function _getSize()
    {
        if (!$this->_fileSize)
        {
            // todo: убрать этот костыль!
            $this->_fileSize = exec('stat -c %s "' . str_replace('"', '\"', $this->path()));   
        }
        
        return $this->_fileSize;
    }

    public function getSize()
    {
        if ($this->getName() === '..' || Dir::isDir($this->path()))
        {
            return false;
        }

        return Str::fileSize($this->_getSize());
    }

    public function getTime()
    {
        if ($this->getName() === '..')
        {
            return false;
        }

        return date('d.m.Y H:i:s', $this->stat('mtime'));
    }

    public function getName()
    {
        return $this->name;
    }

}
