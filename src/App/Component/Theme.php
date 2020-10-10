<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Component;

use CrazyCat\Framework\App\Area;
use CrazyCat\Framework\App\Component\Theme\Page;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     https://crazy-cat.cn
 */
class Theme extends \CrazyCat\Framework\App\Data\DataObject
{
    /**
     * Caches of static files
     */
    public const CACHE_STATIC_URL_NAME = 'static_url';
    public const CACHE_STATIC_FILE_NAME = 'static_file';

    /**
     * Root of static files
     */
    public const DIR_STATIC = 'static';

    /**
     * Config file name
     */
    public const FILE_CONFIG = 'config' . DS . 'theme.php';

    /**
     * @var array
     */
    private $configRules = [
        'area'  => ['required' => true, 'type' => 'string'],
        'alias' => ['required' => true, 'type' => 'string']
    ];

    /**
     * @var \CrazyCat\Framework\App\Component\Module\Manager
     */
    private $moduleManager;

    /**
     * @var \CrazyCat\Framework\App\ObjectManager
     */
    private $objectManager;

    /**
     * @var \CrazyCat\Framework\App\Component\Theme\Page
     */
    private $page;

    /**
     * @var \CrazyCat\Framework\App\Cache\AbstractCache
     */
    private $staticFileCache;

    /**
     * @var \CrazyCat\Framework\App\Cache\AbstractCache
     */
    private $staticUrlCache;

    /**
     * @var \CrazyCat\Framework\App\Io\Http\Url
     */
    private $url;

    public function __construct(
        \CrazyCat\Framework\App\Cache\Manager $cacheManager,
        \CrazyCat\Framework\App\Component\Module\Manager $moduleManager,
        \CrazyCat\Framework\App\Io\Http\Url $url,
        \CrazyCat\Framework\App\ObjectManager $objectManager,
        array $data
    ) {
        parent::__construct($this->init($data));

        $this->moduleManager = $moduleManager;
        $this->objectManager = $objectManager;
        $this->staticFileCache = $cacheManager->create(
            $this->getData('config')['area'] . '_' . self::CACHE_STATIC_FILE_NAME
        );
        $this->staticUrlCache = $cacheManager->create(
            $this->getData('config')['area'] . '_' . self::CACHE_STATIC_URL_NAME
        );
        $this->url = $url;

        register_shutdown_function([$this->staticFileCache, 'save']);
        register_shutdown_function([$this->staticUrlCache, 'save']);
    }

    /**
     * @param array $data
     * @return array
     * @throws \Exception
     */
    private function verifyConfig($data)
    {
        if (!is_file($data['dir'] . DS . self::FILE_CONFIG)) {
            throw new \Exception(sprintf('Config file of theme `%s` does not exist.', $data['name']));
        }
        $config = require $data['dir'] . DS . self::FILE_CONFIG;

        if (!is_array($config)) {
            throw new \Exception(sprintf('Invalidated config file of theme `%s`.', $data['name']));
        }
        foreach ($config as $key => $value) {
            if (!isset($this->configRules[$key])) {
                unset($config[$key]);
            } elseif (gettype($value) != $this->configRules[$key]['type']) {
                throw new \Exception(sprintf('Invalidated setting `%s` of theme `%s`.', $key, $data['name']));
            }
        }
        foreach ($this->configRules as $key => $rule) {
            if ($rule['required'] && !isset($config[$key])) {
                throw new \Exception(sprintf('Setting `%s` of theme `%s` is required.', $key, $data['name']));
            }
        }
        if (!in_array($config['area'], [Area::CODE_FRONTEND, Area::CODE_BACKEND])) {
            throw new \Exception(sprintf('Invalidated area of theme `%s`.', $key, $data['name']));
        }
        return $config;
    }

    /**
     * @param array $data
     * @return array
     * @throws \Exception
     */
    private function init($data)
    {
        /**
         * Consider the theme data is got from cache and skip
         *     initializing actions when it is with `config`.
         */
        if (!isset($data['config'])) {
            $data['config'] = $this->verifyConfig($data);

            /**
             * Use alias as theme name, because the unique component
             *     name does not make sence for a theme.
             */
            $data['name'] = $data['config']['alias'];
        }

        return $data;
    }

    /**
     * @return \CrazyCat\Framework\App\Component\Theme\Page
     * @throws \ReflectionException
     */
    public function getPage()
    {
        if ($this->page === null) {
            $this->page = $this->objectManager->create(Page::class, ['theme' => $this]);
        }
        return $this->page;
    }

    /**
     * @param string $targetFile
     * @param string $sourceFile
     */
    private function generateSymlink($targetFile, $sourceFile)
    {
        if (is_file($sourceFile)) {
            $targetDir = dirname($targetFile);
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
            symlink($sourceFile, $targetFile);
        }
    }

    /**
     * @param string $path
     * @return string
     */
    public function getStaticPath($path)
    {
        if (($file = $this->staticFileCache->getData($path))) {
            return $file;
        }

        $themeArea = $this->getData('config')['area'];

        /**
         * Static files in module
         */
        if (($pos = strpos($path, '::')) !== false &&
            ($module = $this->moduleManager->getModule(trim(substr($path, 0, $pos))))) {
            $file = $module->getData('dir') . DS . 'view' . DS . $themeArea . DS . 'web' . DS . substr($path, $pos + 2);
            return is_file($file) ? $file : null;
        } else {
            /**
             * Static files in theme
             */
            $file = $this->getData('dir') . DS . 'view' . DS . 'web' . DS . $path;
            return is_file($file) ? $file : null;
        }
    }

    /**
     * @param string $themeArea
     * @param string $themeName
     * @param string $path
     * @return string
     */
    public function generateStaticFile($themeArea, $themeName, $path)
    {
        /**
         * Static files in module
         */
        if (($pos = strpos($path, '::')) !== false &&
            ($module = $this->moduleManager->getModule(trim(substr($path, 0, $pos))))) {
            $relatedFilePath = str_replace(['\\', '::'], '/', $path);
            $targetFile = DIR_PUB . DS . self::DIR_STATIC . DS . $themeArea . DS . $themeName . DS . $relatedFilePath;
            $sourceFile = $module->getData('dir') .
                DS . 'view' . DS . $themeArea . DS . 'web' . DS . substr($path, $pos + 2);
            if (!is_file($targetFile) && is_file($sourceFile)) {
                $this->generateSymlink($targetFile, $sourceFile);
                $this->staticFileCache->setData($path, $sourceFile);
            }
        } else {
            /**
             * Static files in theme
             */
            $relatedFilePath = $path;
            $targetFile = DIR_PUB . DS . self::DIR_STATIC . DS . $themeArea . DS . $themeName . DS . $relatedFilePath;
            $sourceFile = $this->getData('dir') . DS . 'view' . DS . 'web' . DS . $relatedFilePath;
            if (!is_file($targetFile) && is_file($sourceFile)) {
                $this->generateSymlink($targetFile, $sourceFile);
                $this->staticFileCache->setData($path, $sourceFile);
            }
        }

        return $relatedFilePath;
    }

    /**
     * @param string $path
     * @return string
     */
    public function getStaticUrl($path)
    {
        if (($url = $this->staticUrlCache->getData($path))) {
            return $url;
        }

        $themeArea = $this->getData('config')['area'];
        $themeName = $this->getData('name');

        $relatedFilePath = $this->generateStaticFile($themeArea, $themeName, $path);
        $url = $this->url->getBaseUrl() . 'static/' . $themeArea . '/' . $themeName . '/' . $relatedFilePath;
        $this->staticUrlCache->setData($path, $url);

        return $url;
    }
}
