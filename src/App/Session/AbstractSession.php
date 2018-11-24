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
     * @var \CrazyCat\Framework\App\Session\Storage
     */
    protected $storage;

    public function __construct( ObjectManager $objectManager, Manager $manager )
    {
        $this->storage = $objectManager->create( Storage::class, [ 'namespace' => static::NAME ] );

        $manager->init();
    }

    /**
     * @param string $key
     * @param boolean $clear
     * @return mixed
     */
    public function getData( $key, $clear = false )
    {
        $data = $this->storage->getData( $key );
        if ( $clear ) {
            $this->storage->unsetData( $key );
        }
        return $data;
    }

}
