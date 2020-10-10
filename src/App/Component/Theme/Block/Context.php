<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Component\Theme\Block;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     https://crazy-cat.cn
 */
class Context
{
    /**
     * @var \CrazyCat\Framework\App\Area
     */
    protected $area;

    /**
     * @var \CrazyCat\Framework\App\Cache\Manager
     */
    protected $cacheManager;

    /**
     * @var \CrazyCat\Framework\App\EventManager
     */
    protected $eventManager;

    /**
     * @var \CrazyCat\Framework\App\Component\Module\Manager
     */
    private $moduleManager;

    /**
     * @var \CrazyCat\Framework\App\Registry
     */
    protected $registry;

    /**
     * @var \CrazyCat\Framework\App\Io\Http\Request
     */
    protected $request;

    /**
     * @var \CrazyCat\Framework\App\Component\Theme\Manager
     */
    protected $themeManager;

    /**
     * @var \CrazyCat\Framework\App\Component\Language\Translator
     */
    protected $translator;

    /**
     * @var \CrazyCat\Framework\App\Io\Http\Url
     */
    protected $url;

    public function __construct(
        \CrazyCat\Framework\App\Area $area,
        \CrazyCat\Framework\App\Cache\Manager $cacheManager,
        \CrazyCat\Framework\App\Component\Language\Translator $translator,
        \CrazyCat\Framework\App\Component\Theme\Manager $themeManager,
        \CrazyCat\Framework\App\EventManager $eventManager,
        \CrazyCat\Framework\App\Io\Http\Request $request,
        \CrazyCat\Framework\App\Component\Module\Manager $moduleManager,
        \CrazyCat\Framework\App\Registry $registry,
        \CrazyCat\Framework\App\Io\Http\Url $url
    ) {
        $this->area = $area;
        $this->cacheManager = $cacheManager;
        $this->eventManager = $eventManager;
        $this->moduleManager = $moduleManager;
        $this->registry = $registry;
        $this->request = $request;
        $this->themeManager = $themeManager;
        $this->translator = $translator;
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
     * @return \CrazyCat\Framework\App\Cache\Manager
     */
    public function getCacheManager()
    {
        return $this->cacheManager;
    }

    /**
     * @return \CrazyCat\Framework\App\EventManager
     */
    public function getEventManager()
    {
        return $this->eventManager;
    }

    /**
     * @return \CrazyCat\Framework\App\Component\Module\Manager
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
     * @return \CrazyCat\Framework\App\Io\Http\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return \CrazyCat\Framework\App\Component\Theme\Manager
     */
    public function getThemeManager()
    {
        return $this->themeManager;
    }

    /**
     * @return \CrazyCat\Framework\App\Component\Language\Translator
     */
    public function getTranslator()
    {
        return $this->translator;
    }

    /**
     * @return \CrazyCat\Framework\App\Io\Http\Url
     */
    public function getUrl()
    {
        return $this->url;
    }
}
