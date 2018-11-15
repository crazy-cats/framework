<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App;

use CrazyCat\Framework\App\Cache\Factory as CacheFactory;
use CrazyCat\Framework\App\Module\Manager as ModuleManager;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class Translator {

    const CACHE_LANG_NAME = 'languages';
    const CACHE_TRANSLATIONS_NAME = 'translations';

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
     * @var string
     */
    private $langCode = 'en_US';

    /**
     * @var array
     */
    private $langPackages = [];

    public function __construct( CacheFactory $cacheFactory, ModuleManager $moduleManager )
    {
        $this->cache = $cacheFactory->create( self::CACHE_LANG_NAME );
        $this->cacheFactory = $cacheFactory;
        $this->moduleManager = $moduleManager;
    }

    /**
     * @param string $dir
     * @param string $langCode
     * @return array
     */
    private function collectTranslations( $dir, $langCode )
    {
        if ( !is_file( $dir . DS . $langCode . '.csv' ) ) {
            return [];
        }

        $translations = [];
        $fp = fopen( $dir . DS . $langCode . '.csv', 'r' );
        while ( $row = fgetcsv( $fp ) ) {
            if ( count( $row ) >= 2 ) {
                $translations[$row[0]] = $row[1];
            }
        }
        fclose( $fp );

        return $translations;
    }

    private function getTranslations( $langCode )
    {
        if ( !isset( $this->translationsCaches[$langCode] ) ) {
            $this->translationsCaches[$langCode] = $this->cacheFactory->create( self::CACHE_TRANSLATIONS_NAME . '-' . $langCode );
        }

        if ( empty( $this->translationsCaches[$langCode]->getData() ) ) {
            $translations = [];

            /**
             * Translations in language packages
             */
            foreach ( $this->langPackages as $package ) {
                $translations = array_merge( $translations, $this->collectTranslations( $package['dir'] . DS . 'i18n', $langCode ) );
            }

            /**
             * Translations in modules
             */
            foreach ( $this->moduleManager->getEnabledModules() as $module ) {
                $translations = array_merge( $translations, $this->collectTranslations( $module->getData( 'dir' ) . DS . 'i18n', $langCode ) );
            }

            $this->translationsCaches[$langCode]->setData( $translations )->save();
        }

        return $this->translationsCaches[$langCode]->getData();
    }

    /**
     * @param string $langCode
     * @return $this
     */
    public function setLangCode( $langCode )
    {
        if ( isset( $this->langPackages[$langCode] ) ) {
            $this->langCode = $langCode;
        }

        return $this;
    }

    /**
     * @param array $languageSource
     */
    public function init( $languageSource )
    {
        if ( empty( $this->langPackages = $this->cache->getData() ) ) {
            foreach ( $languageSource as $language ) {
                $config = require $language['dir'] . DS . 'config.php';
                $this->langPackages[$config['code']] = [
                    'dir' => $language['dir'],
                    'code' => $config['code']
                ];
            }
            $this->cache->setData( $this->langPackages )->save();
        }
    }

    /**
     * @param string $text
     * @param array $variables
     * @param string|null $langCode
     * @return string
     */
    public function translate( $text, array $variables = [], $langCode = null )
    {
        if ( $langCode === null ) {
            $langCode = $this->langCode;
        }

        $translations = $this->getTranslations( $langCode );
        $translatedText = isset( $translations[$text] ) ? $translations[$text] : $text;
        for ( $k = 0; $k < count( $variables ); $k ++ ) {
            $i = $k + 1;
            $translatedText = str_replace( "%$i", $variables[$k], $translatedText );
        }
        return $translatedText;
    }

}
