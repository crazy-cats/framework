<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Module\Block\Backend;

use CrazyCat\Framework\App\Area;
use CrazyCat\Framework\App\Cache\Factory as CacheFactory;
use CrazyCat\Framework\App\EventManager;
use CrazyCat\Framework\App\Io\Http\Request;
use CrazyCat\Framework\App\Module\Manager as ModuleManager;
use CrazyCat\Framework\App\ObjectManager;
use CrazyCat\Framework\App\Registry;
use CrazyCat\Framework\App\Theme\Manager as ThemeManager;
use CrazyCat\Framework\App\Url;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class Context extends \CrazyCat\Framework\App\Theme\Block\Context {

    /**
     * @var \CrazyCat\Framework\App\ObjectManager
     */
    protected $objectManager;

    public function __construct( ObjectManager $objectManager, Area $area, Request $request, Registry $registry, CacheFactory $cacheFactory, ModuleManager $moduleManager, ThemeManager $themeManager, Url $url, EventManager $eventManager )
    {
        parent::__construct( $area, $request, $registry, $cacheFactory, $moduleManager, $themeManager, $url, $eventManager );

        $this->objectManager = $objectManager;
    }

    /**
     * @return \CrazyCat\Framework\App\ObjectManager
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }

}
