<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Theme\Block;

use CrazyCat\Framework\App\Area;
use CrazyCat\Framework\App\Cache\Factory as CacheFactory;
use CrazyCat\Framework\App\EventManager;
use CrazyCat\Framework\App\Module\Manager as ModuleManager;
use CrazyCat\Framework\App\Registry;
use CrazyCat\Framework\App\Theme\Manager as ThemeManager;
use CrazyCat\Framework\App\Url;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class Context {

    /**
     * @var \CrazyCat\Framework\App\Area
     */
    protected $area;

    /**
     * @var \CrazyCat\Framework\App\Cache\Factory
     */
    protected $cacheFactory;

    /**
     * @var \CrazyCat\Framework\App\EventManager
     */
    protected $eventManager;

    /**
     * @var \CrazyCat\Framework\App\Module\Manager
     */
    private $moduleManager;

    /**
     * @var \CrazyCat\Framework\App\Registry
     */
    protected $registry;

    /**
     * @var \CrazyCat\Framework\App\Theme\Manager
     */
    protected $themeManager;

    /**
     * @var \CrazyCat\Framework\App\Url
     */
    protected $url;

    public function __construct( Area $area, Registry $registry, CacheFactory $cacheFactory, ModuleManager $moduleManager, ThemeManager $themeManager, Url $url, EventManager $eventManager )
    {
        $this->area = $area;
        $this->cacheFactory = $cacheFactory;
        $this->eventManager = $eventManager;
        $this->moduleManager = $moduleManager;
        $this->registry = $registry;
        $this->themeManager = $themeManager;
        $this->url = $url;
    }

    /**
     * @return \CrazyCat\Framework\App\Area
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * @return \CrazyCat\Framework\App\Cache\Factory
     */
    public function getCacheFactory()
    {
        return $this->cacheFactory;
    }

    /**
     * @return \CrazyCat\Framework\App\EventManager
     */
    public function getEventManager()
    {
        return $this->eventManager;
    }

    /**
     * @return \CrazyCat\Framework\App\Module\Manager
     */
    public function getModuleManager()
    {
        return $this->moduleManager;
    }

    /**
     * @return \CrazyCat\Framework\App\Registry
     */
    public function getRegistry()
    {
        return $this->registry;
    }

    /**
     * @return \CrazyCat\Framework\App\Theme\Manager
     */
    public function getThemeManager()
    {
        return $this->themeManager;
    }

    /**
     * @return \CrazyCat\Framework\App\Url
     */
    public function getUrl()
    {
        return $this->url;
    }

}
