<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App;

use CrazyCat\Framework\App;
use CrazyCat\Framework\App\Cache\Factory as CacheFactory;
use CrazyCat\Framework\App\Module\Manager as ModuleManager;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class Translation {

    const CACHE_NAME = 'translations';

    /**
     * @var \CrazyCat\Framework\App\Cache\Factory
     */
    private $cacheFactory;

    /**
     * @var \CrazyCat\Framework\App\Cache\AbstractCache[]
     */
    private $caches;

    /**
     * @var \CrazyCat\Framework\App\Module\Manager
     */
    private $moduleManager;

    /**
     * @var string
     */
    private $langCode = 'en_US';

    /**
     * @var string[]
     */
    private $langCodes = [ 'en_US' ];

    public function __construct( CacheFactory $cacheFactory, ModuleManager $moduleManager )
    {
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
        if ( !isset( $this->caches[$langCode] ) ) {
            $this->caches[$langCode] = $this->cacheFactory->create( self::CACHE_NAME . '-' . $langCode );
        }

        if ( empty( $this->caches[$langCode]->getData() ) ) {

            /**
             * Translations in framework
             */
            $translations = $this->collectTranslations( App::DIR . DS . 'i18n', $langCode );

            /**
             * Translations in modules
             */
            foreach ( $this->moduleManager->getEnabledModules() as $module ) {
                $translations = array_merge( $translations, $this->collectTranslations( $module->getData( 'dir' ) . DS . 'i18n', $langCode ) );
            }

            $this->caches[$langCode]->setData( $translations )->save();
        }

        return $this->caches[$langCode]->getData();
    }

    /**
     * @param string $langCode
     * @return $this
     */
    public function setLangCode( $langCode )
    {
        if ( in_array( $langCode, $this->langCodes ) ) {
            $this->langCode = $langCode;
        }

        return $this;
    }

    /**
     * @return void
     */
    public function init()
    {
        $this->getTranslations( $this->langCode );
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

        $translations = $this->caches[$langCode]->getData();
        $translatedText = isset( $translations[$text] ) ? $translations[$text] : $text;
        for ( $k = 0; $k < count( $variables ); $k ++ ) {
            $i = $k + 1;
            $translatedText = str_replace( "%$i", $variables[$k], $translatedText );
        }
        return $translatedText;
    }

}
