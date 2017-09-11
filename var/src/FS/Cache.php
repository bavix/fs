<?php

namespace Bavix\FS;

use Bavix\Kernel\Common;
use Stash\Driver\AbstractDriver;
use Stash\Driver\Composite;
use Stash\Driver\Apc;
use Stash\Driver\FileSystem;
use Stash\Driver\Redis;
use Stash\Pool;

class Cache
{

    /**
     * @var Pool
     */
    protected $pool;
    protected $driver;

    /**
     * Cache constructor.
     */
    public function __construct()
    {
        $driver  = new Composite([
            'drivers' => [
                $this->driver()
            ]
        ]);

        $this->pool = new Pool($driver);
    }

    /**
     * @return AbstractDriver
     */
    protected function driver(): AbstractDriver
    {
        if (!$this->driver)
        {
            $this->driver = new Redis([
                // apc
//                'namespace' => $this->ns(),
//                'ttl'       => 2592000,

                // redis
//                'path' => Common::var() . 'cache/',
            ]);
        }

        return $this->driver;
    }

    /**
     * @return string
     */
    protected function ns($path = null)
    {
        return hash('sha256', $path ?: __FILE__);
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function get($path)
    {
        return $this->pool
            ->getItem($this->ns($path))
            ->get();
    }

    /**
     * @param string $path
     * @param string $value
     *
     * @return bool
     */
    public function set($path, $value)
    {
        if (!$value)
        {
            return false;
        }

        $item = $this->pool
            ->getItem($this->ns($path))
            ->set($value);

        return $this->pool->save($item);
    }

}
