<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Io\Http\Session;

use CrazyCat\Framework\App\ObjectManager;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     http://crazy-cat.cn
 */
abstract class AbstractSession
{
    /**
     * @var \CrazyCat\Framework\App\Io\Http\Session\Manager
     */
    protected $manager;

    /**
     * @var \CrazyCat\Framework\App\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \CrazyCat\Framework\App\Io\Http\Session\Storage
     */
    protected $storage;

    public function __construct(ObjectManager $objectManager, Manager $manager)
    {
        $this->manager = $manager;
        $this->objectManager = $objectManager;
        $this->storage = $objectManager->create(Storage::class, ['namespace' => static::NAME]);

        $this->manager->init();
        $this->storage->init();
    }

    public function clearData()
    {
        $this->storage->clearData();
    }

    public function destroy()
    {
        $this->storage->destroy();
        $this->manager->destroy();
    }
}
