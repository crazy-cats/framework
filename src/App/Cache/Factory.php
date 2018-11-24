<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Cache;

use CrazyCat\Framework\App\Area;
use CrazyCat\Framework\App\Config;
use CrazyCat\Framework\App\ObjectManager;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class Factory {

    private $config;
    private $objectManager;

    public function __construct( Config $config, ObjectManager $objectManager )
    {
        $this->config = $config;
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $name
     * @return \CrazyCat\Framework\App\Cache\AbstractCache
     */
    public function create( $name )
    {
        $settings = $this->config->getData( Area::CODE_GLOBAL )['cache'];
        switch ( $settings['type'] ) {
            default:
                $className = File::class;
                break;
        }
        return $this->objectManager->create( $className, [ 'name' => $name, 'config' => isset( $settings['config'] ) ? $settings['config'] : [] ] );
    }

}
