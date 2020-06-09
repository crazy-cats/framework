<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Io\Http\Session\SaveHandler;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     https://crazy-cat.cn
 */
class Redis extends AbstractHandler
{
    public const TYPE = 'redis';

    protected function init()
    {
        session_module_name('redis');
    }

    public function open($savePath, $name)
    {
    }

    public function read($sessionId)
    {
    }

    public function write($sessionId, $sessionData)
    {
    }

    public function close()
    {
    }

    public function destroy($sessionId)
    {
    }

    public function gc($maxLifetime)
    {
    }
}
