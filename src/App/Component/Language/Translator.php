<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Component\Language;

use CrazyCat\Framework\App\Area;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     https://crazy-cat.cn
 */
class Translator
{
    public const CACHE_LANG_NAME = 'languages';
    public const CACHE_TRANSLATIONS_NAME = 'translations';

    public const DIR = 'i18n';
    public const REQUEST_KEY = 'lang';

    /**
     * @var \CrazyCat\Framework\App\Area
     */
    private $area;

    /**
     * @var \CrazyCat\Framework\App\Cache\Manager
     */
    private $cacheManager;

    /**
     * @var \CrazyCat\Framework\App\Cache\AbstractCache
     */
    private $cache;

    /**
     * @var \CrazyCat\Framework\Utility\File
     */
    private $fileHelper;

    /**
     * @var \CrazyCat\Framework\App\Cache\AbstractCache[]
     */
    private $translationsCaches;

    /**
     * @var \CrazyCat\Framework\App\Component\Module\Manager
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
        \CrazyCat\Framework\App\Area $area,
        \CrazyCat\Framework\App\Cache\Manager $cacheManager,
        \CrazyCat\Framework\App\Component\Module\Manager $moduleManager,
        \CrazyCat\Framework\App\Component\Theme\Manager $themeManager,
        \CrazyCat\Framework\App\Config $config,
        \CrazyCat\Framework\Utility\File $fileHelper
    ) {
        $this->area = $area;
        $this->cache = $cacheManager->create(self::CACHE_LANG_NAME);
        $this->cacheManager = $cacheManager;
        $this->fileHelper = $fileHelper;
        $this->moduleManager = $moduleManager;
        $this->themeManager = $themeManager;

        $settings = $config->getValue(Area::CODE_GLOBAL);
        $this->langCode = $settings['lang'];
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
        while ($row = $this->fileHelper->getCsv($fp)) {
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
            $this->translationsCaches[$cacheKey] = $this->cacheManager->create(
                self::CACHE_TRANSLATIONS_NAME . '-' . $cacheKey
            );
        }

        if (empty($this->translationsCaches[$cacheKey]->getData())) {
            /**
             * Translations in language packages
             */
            $translations = $this->collectTranslations(
                $this->langPackages[$langCode]['dir'] . DS . self::DIR,
                $langCode
            );

            /**
             * Translations in modules
             */
            foreach ($this->moduleManager->getEnabledModules() as $module) {
                $translations = array_merge(
                    $translations,
                    $this->collectTranslations($module->getData('dir') . DS . self::DIR, $langCode)
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
                            $theme->getData('dir') . DS . self::DIR,
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
                    'dir'  => $language['dir'],
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
