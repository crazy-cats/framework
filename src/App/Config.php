<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App;

use CrazyCat\Framework\App\Db\Manager;

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
    const SCOPE_WEBSITE = 'website';
    const SCOPE_STAGE = 'stage';

    /**
     * @var \CrazyCat\Framework\App\Cache\AbstractCache
     */
    private $cache;

    /**
     * @var \CrazyCat\Framework\App\ObjectManager
     */
    private $objectManager;

    public function __construct(
        \CrazyCat\Framework\App\ObjectManager $objectManager
    ) {
        parent::__construct([self::SCOPE_GLOBAL => require DIR_APP . DS . self::DIR . DS . self::FILE]);

        $this->objectManager = $objectManager;
    }

    /**
     * @param string|null $scope global, website, stage
     * @param string|null $id
     * @return Config
     * @throws \ReflectionException
     */
    private function collectConfigData($scope, $id)
    {
        if ($this->cache === null) {
            $cacheManager = $this->objectManager->get(\CrazyCat\Framework\App\Cache\Manager::class);
            $this->cache = $cacheManager->create(self::CACHE_NAME);
        }

        $key = $scope . '-' . $id;
        if (!$this->cache->hasData($key)) {
            /* @var $dbManager \CrazyCat\Framework\App\Db\Manager */
            $dbManager = $this->objectManager->get(\CrazyCat\Framework\App\Db\Manager::class);
            $conn = $dbManager->getConnection();
            $sql = sprintf(
                'SELECT `path`, `value` FROM %s WHERE `scope` = ? AND `id` = ?',
                $conn->getTableName('config')
            );
            $this->cache->setData($key, $conn->fetchPairs($sql, [$scope, $id]))->save();
        }
        $this->setData($key, $this->cache->getData($key));

        return $this;
    }

    /**
     * @param string      $path
     * @param string|null $scope global, website, stage
     * @param string|null $id
     * @return mixed
     * @throws \Exception
     */
    public function getValue($path, $scope = self::SCOPE_GLOBAL, $id = null)
    {
        $globalConfig = $this->getData(self::SCOPE_GLOBAL);
        if ($scope == self::SCOPE_GLOBAL) {
            return $globalConfig[$path] ?? null;
        }
        if (!in_array($scope, [self::SCOPE_WEBSITE, self::SCOPE_STAGE])
            || $id === null
        ) {
            throw new \Exception('Invalid parameter.');
        }
        if (!$this->hasData($scope . '-' . $id)) {
            $this->collectConfigData($scope, $id);
        }
        $config = $this->getData($scope . '-' . $id);
        return $config[$path] ?? ($globalConfig[$path] ?? null);
    }
}
