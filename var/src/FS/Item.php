<?php

namespace Bavix\FS;

use Bavix\Helpers\Dir;
use Bavix\Helpers\File;
use Bavix\Helpers\Str;
use Bavix\SDK\Path;

class Item
{

    protected $uri;

    protected $root;
    protected $name;

    public function __construct($root, $uri, $name)
    {
        $this->uri  = Path::slash($uri);
        $this->root = Path::slash($root);
        $this->name = $name;
    }

    protected function path()
    {
        return $this->root . $this->uri . $this->getName();
    }

    public function getIcon()
    {
        if ($this->getName() === '..' || Dir::isDir($this->path()))
        {
            return 'fa-folder';
        }

        // List of official MIME Types: http://www.iana.org/assignments/media-types/media-types.xhtml
        $font_awesome_file_icon_classes = array(
            // Images
            'image'                    => 'fa-file-image-o',
            // Audio
            'audio'                    => 'fa-file-audio-o',
            // Video
            'video'                    => 'fa-file-video-o',
            // Documents
            'application/pdf'          => 'fa-file-pdf-o',
            'text/plain'               => 'fa-file-text-o',
            'text/html'                => 'fa-file-code-o',
            'application/json'         => 'fa-file-code-o',
            // Archives
            'application/gzip'         => 'fa-file-archive-o',
            'application/zip'          => 'fa-file-archive-o',
            // Misc
            'application/octet-stream' => 'fa-file-o',
        );

        $mime = $this->getMimeType();

        if ($mime)
        {
            if (isset($font_awesome_file_icon_classes[$mime]))
            {
                return $font_awesome_file_icon_classes[$mime];
            }

            $mime_parts = explode('/', $mime, 2);
            $mime_group = $mime_parts[0];

            if (isset($font_awesome_file_icon_classes[$mime_group]))
            {
                return $font_awesome_file_icon_classes[$mime_group];
            }
        }

        return 'fa-file-o';
    }

    public function getMimeType()
    {
        if (File::isFile($this->path()))
            return @finfo_file(finfo_open(FILEINFO_MIME_TYPE), $this->path());

        return null;
    }

    public function getPath()
    {
        if ($this->getName() === '..' || Dir::isDir($this->path()))
        {
            return $this->uri . $this->name;
        }

        return '/' . basename($this->root) . $this->uri . $this->name;
    }

    public function getSize()
    {
        if ($this->getName() === '..' || Dir::isDir($this->path()))
        {
            return false;
        }

        return Str::fileSize(@filesize($this->path()));
    }

    public function getTime()
    {
        if ($this->getName() === '..')
        {
            return false;
        }

        return date('d.m.Y H:i:s', @filemtime($this->path()));
    }

    public function getName()
    {
        return $this->name;
    }

}
