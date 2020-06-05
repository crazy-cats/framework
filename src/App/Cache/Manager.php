<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Cache;

use CrazyCat\Framework\App\Area;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     https://crazy-cat.cn
 */
class Manager
{
    /**
     * @var \CrazyCat\Framework\App\Cache\AbstractCache[]
     */
    private $caches;

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
    }

    /**
     * @param string $name
     * @return \CrazyCat\Framework\App\Cache\AbstractCache
     * @throws \ReflectionException
     */
    public function create($name)
    {
        $config = $this->config->getValue(Area::CODE_GLOBAL)['cache'];
        switch ($config['type']) {
            default:
                $className = Files::class;
                break;
        }

        $cache = $this->objectManager->create($className, ['name' => $name, 'config' => $config]);
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
}
