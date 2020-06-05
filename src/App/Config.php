<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App;

use CrazyCat\Framework\App\Db\MySql;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     https://crazy-cat.cn
 */
class Config extends \CrazyCat\Framework\App\Data\DataObject
{
    const CACHE_NAME = 'config';
    const DIR = 'config';
    const FILE = 'env.php';

    const SCOPE_GLOBAL = 'global';
    const SCOPE_STAGE = 'stage';

    /**
     * @var \CrazyCat\Framework\App\Cache\AbstractCache
     */
    protected $cache;

    /**
     * @var \CrazyCat\Framework\App\ObjectManager
     */
    protected $objectManager;

    public function __construct(
        \CrazyCat\Framework\App\ObjectManager $objectManager
    ) {
        parent::__construct([self::SCOPE_GLOBAL => require DIR_APP . DS . self::DIR . DS . self::FILE]);

        $this->objectManager = $objectManager;
    }

    /**
     * @param string|null $scope global, website, stage
     * @param string|null $stageId
     * @return Config
     * @throws \ReflectionException
     */
    protected function collectConfigData($scope, $stageId)
    {
        if ($this->cache === null) {
            $cacheManager = $this->objectManager->get(\CrazyCat\Framework\App\Cache\Manager::class);
            $this->cache = $cacheManager->create(self::CACHE_NAME);
        }
        return $this;
    }

    /**
     * @param string      $path
     * @param string|null $scope global, stage
     * @param string|null $stageId
     * @return mixed
     * @throws \Exception
     */
    public function getValue($path, $scope = self::SCOPE_GLOBAL, $stageId = null)
    {
        $globalConfig = $this->getData(self::SCOPE_GLOBAL);
        if ($scope == self::SCOPE_GLOBAL) {
            return $globalConfig[$path] ?? null;
        }
        if (!in_array($scope, [self::SCOPE_WEBSITE, self::SCOPE_STAGE])
            || $stageId === null
        ) {
            throw new \Exception('Invalid parameter.');
        }
        $this->collectConfigData($scope, $stageId);
        $config = $this->cache->getData($scope . '-' . $stageId);
        return $config[$path] ?? ($globalConfig[$path] ?? null);
    }
}
