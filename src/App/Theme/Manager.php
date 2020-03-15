<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Theme;

use CrazyCat\Framework\App\Area;
use CrazyCat\Framework\App\Cache\Factory as CacheFactory;
use CrazyCat\Framework\App\Config;
use CrazyCat\Framework\App\ObjectManager;
use CrazyCat\Framework\App\Setup\Component as ComponentManager;
use CrazyCat\Framework\App\Theme;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     http://crazy-cat.cn
 */
class Manager {

    const CACHE_NAME = 'themes';

    /**
     * @var \CrazyCat\Framework\App\Area
     */
    private $area;

    /**
     * @var \CrazyCat\Framework\App\Cache\AbstractCache
     */
    private $cache;

    /**
     * @var \CrazyCat\Framework\App\Setup\Component
     */
    private $componentManager;

    /**
     * @var \CrazyCat\Framework\App\Config
     */
    private $config;

    /**
     * @var \CrazyCat\Framework\App\ObjectManager
     */
    private $objectManager;

    /**
     * @var \CrazyCat\Framework\App\Theme
     */
    private $currentTheme;

    /**
     * @var array
     */
    private $themes = [
        Area::CODE_FRONTEND => [],
        Area::CODE_BACKEND => []
    ];

    public function __construct( Config $config, CacheFactory $cacheFactory, ObjectManager $objectManager, Area $area, ComponentManager $componentManager )
    {
        $this->area = $area;
        $this->cache = $cacheFactory->create( self::CACHE_NAME );
        $this->componentManager = $componentManager;
        $this->config = $config;
        $this->objectManager = $objectManager;
    }

    /**
     * @return $this
     */
    public function init()
    {
        if ( empty( $themesData = $this->cache->getData() ) ) {
            $themesData = [
                Area::CODE_FRONTEND => [],
                Area::CODE_BACKEND => []
            ];
            foreach ( $this->componentManager->getComponents( ComponentManager::TYPE_THEME ) as $themeInfo ) {
                /* @var $theme \CrazyCat\Framework\App\Theme */
                $theme = $this->objectManager->create( Theme::class, [ 'data' => $themeInfo ] );
                $themeArea = $theme->getData( 'config' )['area'];
                $themesData[$themeArea][$theme->getData( 'name' )] = $theme->getData();
                $this->themes[$themeArea][$theme->getData( 'name' )] = $theme;
            }
            $this->cache->setData( $themesData )->save();
        }
        else {
            foreach ( $themesData as $themeArea => $themeGroupData ) {
                foreach ( $themeGroupData as $themeData ) {
                    $this->themes[$themeArea][$themeData['name']] = $this->objectManager->create( Theme::class, [ 'data' => $themeData ] );
                }
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getAllThemes()
    {
        return $this->themes;
    }

    /**
     * @return \CrazyCat\Framework\App\Theme[]
     */
    public function getThemes( $areaCode )
    {
        if ( !isset( $this->themes[$areaCode] ) ) {
            throw new \Exception( 'Invalidated area code for theme.' );
        }
        return $this->themes[$areaCode];
    }

    /**
     * @return \CrazyCat\Framework\App\Theme
     */
    public function getTheme( $areaCode, $themeName )
    {
        if ( !isset( $this->themes[$areaCode] ) ) {
            throw new \Exception( 'Invalidated area code for theme.' );
        }
        if ( !isset( $this->themes[$areaCode][$themeName] ) ) {
            throw new \Exception( 'Specified theme does not exist.' );
        }
        return $this->themes[$areaCode][$themeName];
    }

    /**
     * @return \CrazyCat\Framework\App\Theme|null
     */
    public function getCurrentTheme()
    {
        if ( $this->currentTheme === null ) {
            $themeName = $this->config->getData( $this->area->getCode() )['theme'];
            if ( !isset( $this->themes[$this->area->getCode()][$themeName] ) ) {
                throw new \Exception( 'Specified theme does not exist.' );
            }
            $this->currentTheme = $this->themes[$this->area->getCode()][$themeName];
        }
        return $this->currentTheme;
    }

    /**
     * @param string $themeName
     * @return $this
     */
    public function setCurrentTheme( $themeName )
    {
        if ( !isset( $this->themes[$this->area->getCode()][$themeName] ) ) {
            throw new \Exception( 'Specified theme does not exist.' );
        }
        $this->currentTheme = $this->themes[$this->area->getCode()][$themeName];

        return $this;
    }

}
