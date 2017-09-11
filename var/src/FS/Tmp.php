<?php

namespace Bavix\FS;

use Bavix\Exceptions\Runtime;
use Bavix\Helpers\Str;
use Bavix\SDK\Path;

class Tmp
{

    /**
     * @var string
     */
    protected $tmp;

    /**
     * Tmp constructor.
     *
     * @param string $string
     *
     * @throws Runtime
     */
    public function __construct(string $string)
    {
        $this->tmp = Path::slash(sys_get_temp_dir()) . hash('md5', $string) . '.bxfs';
//        \register_shutdown_function([$this, '__destruct']);
    }

//    /**
//     * shutdown
//     */
//    public function __destruct()
//    {
//        @unlink($this->tmp);
//    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->tmp;
    }

}
