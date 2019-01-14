<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Setup;

use CrazyCat\Framework\App\ObjectManager;
use CrazyCat\Framework\App\Cache\Factory as CacheFactory;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class Update {

    /**
     * This method is run on `post-create-project-cmd` event which
     *      dispatched after composer `create-project` command executed.
     * 
     * @return void
     */
    static public function execute()
    {
        require_once 'definitions';

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
