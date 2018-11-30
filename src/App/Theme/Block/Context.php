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
use CrazyCat\Framework\App\Theme\Manager as ThemeManager;

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
     * @var \CrazyCat\Framework\App\Theme\Manager
     */
    protected $themeManager;

    public function __construct( Area $area, CacheFactory $cacheFactory, ModuleManager $moduleManager, ThemeManager $themeManager, EventManager $eventManager )
    {
        $this->area = $area;
        $this->cacheFactory = $cacheFactory;
        $this->eventManager = $eventManager;
        $this->moduleManager = $moduleManager;
        $this->themeManager = $themeManager;
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
     * @return \CrazyCat\Framework\App\Theme\Manager
     */
    public function getThemeManager()
    {
        return $this->themeManager;
    }

}
