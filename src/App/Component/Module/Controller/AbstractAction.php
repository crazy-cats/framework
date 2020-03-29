<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Component\Module\Controller;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     https://crazy-cat.cn
 */
abstract class AbstractAction
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

    public function __construct(Context $context)
    {
        $this->area = $context->getArea();
        $this->config = $context->getConfig();
        $this->eventManager = $context->getEventManager();
        $this->logger = $context->getLogger();
        $this->objectManager = $context->getObjectManager();
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    protected function beforeRun()
    {
        $this->eventManager->dispatch(
            'controller_execute_before',
            ['action' => $this]
        );
        $this->eventManager->dispatch(
            sprintf('%s_execute_before', $this->request->getFullPath()),
            ['action' => $this]
        );
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    protected function afterRun()
    {
        $this->eventManager->dispatch(
            sprintf('%s_execute_after', $this->request->getFullPath()),
            ['action' => $this]
        );
        $this->eventManager->dispatch(
            'controller_execute_after',
            ['action' => $this]
        );
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    abstract public function run();
}
