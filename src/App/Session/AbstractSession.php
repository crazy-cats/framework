<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Session;

use CrazyCat\Framework\App\ObjectManager;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
abstract class AbstractSession {

    /**
     * @var \CrazyCat\Framework\App\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \CrazyCat\Framework\App\Session\Storage
     */
    protected $storage;

    public function __construct( ObjectManager $objectManager, Manager $manager )
    {
        $this->objectManager = $objectManager;
        $this->storage = $objectManager->create( Storage::class, [ 'namespace' => static::NAME ] );

        $manager->init();
    }

}
