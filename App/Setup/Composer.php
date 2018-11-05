<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Setup;

use Composer\Script\Event;
use CrazyCat\Framework\App\ObjectManager;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class Composer {

    /**
     * @param \Composer\Script\Event $event
     * @see https://getcomposer.org/apidoc/master/Composer/Script/Event.html
     * @see https://getcomposer.org/apidoc/master/Composer/Autoload/ClassLoader.html
     */
    static public function install( Event $event )
    {
        self::update( $event );
    }

    /**
     * @param \Composer\Script\Event $event
     * @see https://getcomposer.org/apidoc/master/Composer/Script/Event.html
     * @see https://getcomposer.org/apidoc/master/Composer/Autoload/ClassLoader.html
     */
    static public function update( Event $event )
    {
        $composer = $event->getComposer();
        $package = $composer->getPackage();
        $generator = $composer->getAutoloadGenerator();
        $loader = $generator->createLoader( $package->getAutoload() );

        ObjectManager::getInstance()->get( Component::class )->init( $loader, $composer->getConfig()->get( 'vendor-dir' ) . '/..' );
    }

}
