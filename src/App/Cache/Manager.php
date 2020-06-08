<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Cache;

use CrazyCat\Framework\App\Area;
use CrazyCat\Framework\App\Config;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     https://crazy-cat.cn
 */
class Manager
{
    const CONFIG_KEY = 'cache';
    const CONFIG_FILE = 'caches.php';

    /**
     * @var array
     */
    private $cacheStatus;

    /**
     * @var \CrazyCat\Framework\App\Config
     */
    private $config;

    /**
     * @var \CrazyCat\Framework\App\ObjectManager
     */
    private $objectManager;

    public function __construct(
        \CrazyCat\Framework\App\Config $config,
        \CrazyCat\Framework\App\ObjectManager $objectManager
    ) {
        $this->config = $config;
        $this->objectManager = $objectManager;

        $this->init();
    }

    /**
     * @return void
     */
    private function init()
    {
        $file = DIR_APP . DS . Config::DIR . DS . self::CONFIG_FILE;
        if (is_file($file)) {
            $this->cacheStatus = require $file;
        }
        if (!isset($config) || !is_array($config) || empty($config)) {
            $this->cacheStatus = [];
        }
    }

    /**
     * @return void
     */
    private function updateConfig()
    {
        file_put_contents(
            DIR_APP . DS . Config::DIR . DS . self::CONFIG_FILE,
            sprintf("<?php\nreturn %s;\n", $this->config->toString($this->cacheStatus))
        );
    }

    /**
     * @param string $name
     * @return \CrazyCat\Framework\App\Cache\AbstractCache
     * @throws \ReflectionException
     */
    public function create($name)
    {
        $config = $this->config->getValue(Area::CODE_GLOBAL)[self::CONFIG_KEY];
        switch ($config['type']) {
            default:
                $className = Files::class;
                break;
        }

        $cache = $this->objectManager->create($className, ['name' => $name, 'config' => $config]);
        if (!isset($this->cacheStatus[$name])) {
            $this->cacheStatus[$name] = true;
        }
        $cache->status($this->cacheStatus[$name]);
        $this->caches[$name] = $cache;

        return $cache;
    }

    /**
     * @param string $name
     * @return \CrazyCat\Framework\App\Cache\AbstractCache|null
     */
    public function get($name = null)
    {
        if ($name === null) {
            return $this->caches;
        }
        return $this->caches[$name] ?? null;
    }

    /**
     * @return array
     */
    public function getAllCacheNames()
    {
        return array_keys($this->caches);
    }

    /**
     * @return void
     */
    public function flushAll()
    {
        foreach ($this->caches as $cache) {
            $cache->clear();
        }
    }

    /**
     * @param string $cacheName
     * @return void
     */
    public function enable($cacheName)
    {
        $this->get($cacheName)->status(true);
        $this->cacheStatus[$cacheName] = true;
        $this->updateConfig();
    }

    /**
     * @param string $cacheName
     * @return void
     */
    public function disable($cacheName)
    {
        $this->get($cacheName)->status(false);
        $this->cacheStatus[$cacheName] = false;
        $this->updateConfig();
    }

    /**
     * @return void
     */
    public function __destruct()
    {
        $this->updateConfig();
    }
}
