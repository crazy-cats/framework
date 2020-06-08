<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Component\Module\Controller;

use CrazyCat\Framework\App\Io\AbstractRequest;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     https://crazy-cat.cn
 */
abstract class AbstractContext
{
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
     * @var \CrazyCat\Framework\App\Logger
     */
    protected $logger;

    /**
     * @var \CrazyCat\Framework\App\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \CrazyCat\Framework\App\Io\AbstractRequest
     */
    protected $request;

    public function __construct(
        \CrazyCat\Framework\App\Area $area,
        \CrazyCat\Framework\App\Config $config,
        \CrazyCat\Framework\App\EventManager $eventManager,
        \CrazyCat\Framework\App\Io\AbstractRequest $request,
        \CrazyCat\Framework\App\Logger $logger,
        \CrazyCat\Framework\App\ObjectManager $objectManager
    ) {
        $this->area = $area;
        $this->config = $config;
        $this->eventManager = $eventManager;
        $this->logger = $logger;
        $this->objectManager = $objectManager;
        $this->request = $request;
    }

    /**
     * @return \CrazyCat\Framework\App\Area
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * @return \CrazyCat\Framework\App\Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return \CrazyCat\Framework\App\EventManager
     */
    public function getEventManager()
    {
        return $this->eventManager;
    }

    /**
     * @return \CrazyCat\Framework\App\Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @return \CrazyCat\Framework\App\ObjectManager
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }

    /**
     * @return \CrazyCat\Framework\App\ObjectManager
     */
    public function getRequest()
    {
        return $this->request;
    }
}
