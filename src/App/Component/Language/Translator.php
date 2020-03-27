<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Component\Language;

use CrazyCat\Framework\App\Cache\Factory as CacheFactory;
use CrazyCat\Framework\App\Config;
use CrazyCat\Framework\App\Module\Manager as ModuleManager;
use CrazyCat\Framework\App\Component\Theme\Manager as ThemeManager;
use CrazyCat\Framework\Utility\File;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     http://crazy-cat.cn
 */
class Translator
{
    const CACHE_LANG_NAME = 'languages';
    const CACHE_TRANSLATIONS_NAME = 'translations';
    const REQUEST_KEY = 'lang';

    /**
     * @var \CrazyCat\Framework\App\Area
     */
    private $area;

    /**
     * @var \CrazyCat\Framework\App\Cache\Factory
     */
    private $cacheFactory;

    /**
     * @var \CrazyCat\Framework\App\Cache\AbstractCache
     */
    private $cache;

    /**
     * @var \CrazyCat\Framework\App\Cache\AbstractCache[]
     */
    private $translationsCaches;

    /**
     * @var \CrazyCat\Framework\App\Module\Manager
     */
    private $moduleManager;

    /**
     * @var \CrazyCat\Framework\App\Component\Theme\Manager
     */
    private $themeManager;

    /**
     * @var string
     */
    private $langCode;

    /**
     * @var array
     */
    private $langPackages = [];

    public function __construct(
        Config $config,
        Area $area,
        CacheFactory $cacheFactory,
        ModuleManager $moduleManager,
        ThemeManager $themeManager
    ) {
        $this->area = $area;
        $this->cache = $cacheFactory->create(self::CACHE_LANG_NAME);
        $this->cacheFactory = $cacheFactory;
        $this->langCode = $config[$area->getCode()] ? $config[$area->getCode(
        )]['lang'] : $config[Area::CODE_GLOBAL]['lang'];
        $this->moduleManager = $moduleManager;
        $this->themeManager = $themeManager;
    }

    /**
     * @param string $dir
     * @param string $langCode
     * @return array
     */
    private function collectTranslations($dir, $langCode)
    {
        if (!is_file($dir . DS . $langCode . '.csv')) {
            return [];
        }

        $translations = [];
        $fp = fopen($dir . DS . $langCode . '.csv', 'r');
        while ($row = File::getCsv($fp)) {
            if (count($row) >= 2) {
                $translations[$row[0]] = $row[1];
            }
        }
        fclose($fp);

        return $translations;
    }

    /**
     * @param string $langCode
     * @return array
     * @throws \ReflectionException
     */
    public function getTranslations($langCode)
    {
        $cacheKey = $this->area->getCode() . '-' . $langCode;

        if (!isset($this->translationsCaches[$cacheKey])) {
            $this->translationsCaches[$cacheKey] = $this->cacheFactory->create(
                self::CACHE_TRANSLATIONS_NAME . '-' . $cacheKey
            );
        }

        if (empty($this->translationsCaches[$cacheKey]->getData())) {
            /**
             * Translations in language packages
             */
            $translations = $this->collectTranslations($this->langPackages[$langCode]['dir'] . DS . 'i18n', $langCode);

            /**
             * Translations in modules
             */
            foreach ($this->moduleManager->getEnabledModules() as $module) {
                $translations = array_merge(
                    $translations,
                    $this->collectTranslations($module->getData('dir') . DS . 'i18n', $langCode)
                );
            }

            /**
             * Translations in theme
             */
            try {
                if (($theme = $this->themeManager->getCurrentTheme()) !== null) {
                    $translations = array_merge(
                        $translations,
                        $this->collectTranslations(
                            $theme->getData('dir') . DS . 'i18n',
                            $langCode
                        )
                    );
                }
            } catch (\Exception $e) {
                /**
                 * In some case such as on `backend_controller_execute_before` event,
                 *     we need to show a translated string but the theme is not initialized,
                 *     we don't need to break the process.
                 */
            }

            $this->translationsCaches[$cacheKey]->setData($translations)->save();
        }

        return $this->translationsCaches[$cacheKey]->getData();
    }

    /**
     * @return string
     */
    public function getLangCode()
    {
        return $this->langCode;
    }

    /**
     * @param string $langCode
     * @return $this
     * @throws \Exception
     */
    public function setLangCode($langCode)
    {
        if (!isset($this->langPackages[$langCode])) {
            throw new \Exception('Specified language package does not exist.');
        }
        $this->langCode = $langCode;
        return $this;
    }

    /**
     * @return array
     */
    public function getLanguages()
    {
        return $this->langPackages;
    }

    /**
     * @param array $languageSource
     */
    public function init($languageSource)
    {
        if (empty($this->langPackages = $this->cache->getData())) {
            foreach ($languageSource as $language) {
                $config = require $language['dir'] . DS . 'config.php';
                $this->langPackages[$config['code']] = [
                    'dir' => $language['dir'],
                    'code' => $config['code'],
                    'name' => $config['name']
                ];
            }
            $this->cache->setData($this->langPackages)->save();
        }
    }

    /**
     * @param string      $text
     * @param array       $variables
     * @param string|null $langCode
     * @return string
     * @throws \ReflectionException
     */
    public function translate($text, array $variables = [], $langCode = null)
    {
        if ($langCode === null) {
            $langCode = $this->langCode;
        }

        $translations = $this->getTranslations($langCode);
        $translatedText = isset($translations[$text]) ? $translations[$text] : $text;
        for ($k = 0; $k < count($variables); $k++) {
            $i = $k + 1;
            $translatedText = str_replace("%$i", $variables[$k], $translatedText);
        }
        return $translatedText;
    }
}
