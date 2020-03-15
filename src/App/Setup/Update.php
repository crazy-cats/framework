<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Setup;

use CrazyCat\Framework\App\ObjectManager;
use CrazyCat\Framework\App\Cache\Factory as CacheFactory;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     http://crazy-cat.cn
 */
class Update {

    /**
     * @return void
     */
    static public function execute( $event )
    {
        if ( !defined( 'DIR_APP' ) ) {
            require 'definitions';
        }

        if ( !is_file( DIR_APP . DS . 'config' . DS . 'modules.php' ) ) {
            return;
        }

        if ( $event->getOperation()->getPackage()->getType() != 'crazycat-module' ) {
            return;
        }

        $caches = [
            'components', 'modules', 'di', 'languages',
            'backend_menu_data'
        ];
        foreach ( $caches as $cacheName ) {
            try {
                ObjectManager::getInstance()->get( CacheFactory::class )->create( $cacheName )->clear();
            }
            catch ( \Exception $e ) {
                
            }
        }
    }

}
