<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Io\Http\Session;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     https://crazy-cat.cn
 */
class Storage
{
    /**
     * @var string
     */
    private $namespace;

    public function __construct($namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * @return void
     */
    public function init()
    {
        if (!isset($_SESSION[$this->namespace])) {
            $_SESSION[$this->namespace] = [];
        }
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function getData($key = null)
    {
        if ($key === null) {
            return $_SESSION[$this->namespace];
        }
        return isset($_SESSION[$this->namespace][$key]) ? $_SESSION[$this->namespace][$key] : null;
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function setData($key, $value)
    {
        $_SESSION[$this->namespace][$key] = $value;
    }

    /**
     * @param string $key
     */
    public function unsetData($key)
    {
        unset($_SESSION[$this->namespace][$key]);
    }

    /**
     * @return void
     */
    public function clearData()
    {
        $_SESSION[$this->namespace] = [];
    }

    /**
     * @return void
     */
    public function destroy()
    {
        $_SESSION = [];
    }
}
