<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Cache;

use CrazyCat\Framework\App\Area;
use CrazyCat\Framework\App\Config;
use CrazyCat\Framework\App\ObjectManager;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     http://crazy-cat.cn
 */
class Manager
{
    private $config;
    private $objectManager;

    public function __construct(Config $config, ObjectManager $objectManager)
    {
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
        $config = $this->config->getData(Area::CODE_GLOBAL)['cache'];
        switch ($config['type']) {
            default:
                $className = Files::class;
                break;
        }
        return $this->objectManager->create($className, ['name' => $name, 'config' => $config]);
    }
}
