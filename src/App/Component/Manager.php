<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Component;

use CrazyCat\Framework\App\Area;
use CrazyCat\Framework\App\Cache\Manager as CacheManager;
use CrazyCat\Framework\App\ObjectManager;
use CrazyCat\Framework\Utility\File;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     https://crazy-cat.cn
 */
class Manager
{
    const CACHE_NAME = 'components';
    const REG_FILE = 'registration.php';

    /**
     * paths of app folders
     */
    const DIR_APP_MODULES = 'modules';
    const DIR_APP_THEMES = 'themes';

    /**
     * types
     */
    const TYPE_LANG = 'lang';
    const TYPE_MODULE = 'module';
    const TYPE_THEME = 'theme';

    private $components = [
        self::TYPE_LANG   => [],
        self::TYPE_MODULE => [],
        self::TYPE_THEME  => []
    ];

    /**
     * @var \CrazyCat\Framework\App\ObjectManager
     */
    private $objectManager;

    /**
     * Get component setup singleton
     *
     * @return \CrazyCat\Framework\App\Setup\Setup
     * @throws \ReflectionException
     */
    public static function getInstance()
    {
        return ObjectManager::getInstance()->get(self::class);
    }

    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Use this method instead of injecting the cache factory in construct method,
     *     in order not to meet error when registering a component.
     *
     * @return CacheManager
     * @throws \ReflectionException
     */
    private function getCache()
    {
        return $this->objectManager->get(CacheManager::class)
            ->create(self::CACHE_NAME);
    }

    /**
     * @param \Composer\Autoload\ClassLoader $composerLoader
     * @param bool                           $forceGenerate
     * @return array|mixed
     * @throws \ReflectionException
     * @see https://getcomposer.org/apidoc/master/Composer/Autoload/ClassLoader.html
     */
    public function init($composerLoader, $forceGenerate = false)
    {
        $cache = $this->getCache();

        if ($forceGenerate || !($components = $cache->getData())) {
            /**
             * Add modules of which source codes are in `app/modules` as Psr4 packages
             */
            foreach ($composerLoader->getFallbackDirsPsr4() as $dir) {
                if (!is_dir($dir)) {
                    continue;
                }
                foreach (File::getFolders($dir) as $vendor) {
                    foreach (File::getFolders($dir . '/' . $vendor) as $module) {
                        $prefix = $vendor . '\\' . $module . '\\';
                        $path = $dir . DS . $vendor . DS . $module;
                        if (is_file($path . DS . self::REG_FILE)) {
                            require $path . DS . self::REG_FILE;
                            $composerLoader->addPsr4($prefix, $path . DS . 'code');
                        }
                    }
                }
            }

            /**
             * Collect themes of which source code are in `app/themes`,
             *     only backend and frontend area have themes.
             */
            foreach ([Area::CODE_BACKEND, Area::CODE_FRONTEND] as $areaCode) {
                $dir = DIR_APP . DS . self::DIR_APP_THEMES . DS . $areaCode;
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                foreach (File::getFolders($dir) as $path) {
                    if (is_file($dir . DS . $path . DS . self::REG_FILE)) {
                        require $dir . DS . $path . DS . self::REG_FILE;
                    }
                }
            }

            /**
             * Store in cache
             */
            $cache->setData($this->components)->save();
        } else {
            foreach ($components[self::TYPE_MODULE] as $namespace => $component) {
                $composerLoader->addPsr4($namespace . '\\', $component['dir'] . DS . 'code');
            }
            $this->components = $components;
        }

        return $this->components;
    }

    /**
     * @param string $name
     * @param string $type
     * @param string $dir
     * @throws \Exception
     */
    public function register($name, $type, $dir)
    {
        if (!isset($this->components[$type])) {
            throw new \Exception(sprintf('Component type `%s` does not exist.', $type));
        }
        if (isset($this->components[$type][$name])) {
            throw new \Exception(sprintf('Component name `%s` has been used.', $name));
        }
        $this->components[$type][$name] = [
            'dir'  => $dir,
            'name' => $name
        ];
    }

    /**
     * @param string $type
     * @return array
     * @throws \Exception
     */
    public function getComponents($type)
    {
        if (!isset($this->components[$type])) {
            throw new \Exception(sprintf('Component type `%s` does not exist.', $type));
        }
        return $this->components[$type];
    }
}
