<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Cache;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     https://crazy-cat.cn
 */
class Redis extends AbstractCache
{
    /**
     * @var \Redis
     */
    protected static $redis;

    /**
     * @return void
     */
    protected function init()
    {
        if (self::$redis === null) {
            self::$redis = new \Redis();
            self::$redis->connect($this->config['host'], $this->config['port']);
        }
        $this->data = json_decode(self::$redis->get($this->name), true) ?: [];
    }

    /**
     * @return void
     */
    public function save()
    {
        if ($this->isEnabled) {
            self::$redis->set($this->name, json_encode($this->data));
        }
    }

    /**
     * @param bool $force
     * @return void
     */
    public function clear($force = false)
    {
        if (($this->isEnabled || $force)) {
            self::$redis->del($this->name);
        }
    }
}
