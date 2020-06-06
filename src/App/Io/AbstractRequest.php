<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Io;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     https://crazy-cat.cn
 */
abstract class AbstractRequest
{
    /**
     * @var string
     */
    protected $routeName;

    /**
     * @var string
     */
    protected $moduleName;

    /**
     * @var string
     */
    protected $controllerName;

    /**
     * @var string
     */
    protected $actionName;
    /**
     * @var \CrazyCat\Framework\App\Area
     */
    protected $area;
    /**
     * @var \CrazyCat\Framework\App\Config
     */
    protected $config;
    /**
     * @var \CrazyCat\Framework\App\EventManager
     */
    protected $eventManager;
    /**
     * @var \CrazyCat\Framework\App\Component\Module\Manager
     */
    protected $moduleManager;
    /**
     * @var \CrazyCat\Framework\App\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \CrazyCat\Framework\App\Io\Cli\Response
     */
    protected $response;

    public function __construct(
        \CrazyCat\Framework\App\Area $area,
        \CrazyCat\Framework\App\Component\Module\Manager $moduleManager,
        \CrazyCat\Framework\App\Config $config,
        \CrazyCat\Framework\App\EventManager $eventManager,
        \CrazyCat\Framework\App\ObjectManager $objectManager
    ) {
        $this->area = $area;
        $this->config = $config;
        $this->eventManager = $eventManager;
        $this->moduleManager = $moduleManager;
        $this->objectManager = $objectManager;
    }

    /**
     * @return string
     */
    public function getRouteName()
    {
        return $this->routeName;
    }

    /**
     * @return string
     */
    public function getModuleName()
    {
        return $this->moduleName;
    }

    /**
     * @return string
     */
    public function getControllerName()
    {
        return $this->controllerName;
    }

    /**
     * @return string
     */
    public function getActionName()
    {
        return $this->actionName;
    }

    /**
     * @param string $separator
     * @return string
     */
    public function getFullPath($separator = '_')
    {
        return $this->getRouteName() .
            $separator .
            $this->getControllerName() .
            $separator .
            $this->getActionName();
    }

    /**
     * @return \CrazyCat\Framework\App\Io\AbstractResponse|null
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return void
     */
    abstract public function process();
}
