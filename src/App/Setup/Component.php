<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Setup;

use CrazyCat\Framework\App\Cache\Factory as CacheFactory;
use CrazyCat\Framework\App\ObjectManager;
use CrazyCat\Framework\Utility\File;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class Component {

    const CACHE_NAME = 'components';

    /**
     * paths of app folders
     */
    const DIR_APP_MODULES = DIR_APP . DS . 'modules';
    const DIR_APP_THEMES = DIR_APP . DS . 'themes';

    /**
     * types
     */
    const TYPE_LANG = 'lang';
    const TYPE_MODULE = 'module';
    const TYPE_THEME = 'theme';

    private $components = [
        self::TYPE_LANG => [],
        self::TYPE_MODULE => [],
        self::TYPE_THEME => []
    ];

    /**
     * @var \CrazyCat\Framework\App\Cache\Factory
     */
    private $cacheFactory;

    /**
     * @var \CrazyCat\Framework\App\ObjectManager
     */
    private $objectManager;

    /**
     * Get component setup singleton
     * @return \CrazyCat\Framework\App\Setup\Component
     */
    static public function getInstance()
    {
        return ObjectManager::getInstance()->get( self::class );
    }

    public function __construct( CacheFactory $cacheFactory, ObjectManager $objectManager )
    {
        $this->cacheFactory = $cacheFactory;
        $this->objectManager = $objectManager;
    }

    /**
     * @param \Composer\Autoload\ClassLoader $composerLoader
     * @param boolean $forceGenerate
     * @see https://getcomposer.org/apidoc/master/Composer/Autoload/ClassLoader.html
     */
    public function init( $composerLoader, $forceGenerate = false )
    {
        $cache = $this->cacheFactory->create( self::CACHE_NAME );

        if ( $forceGenerate || !( $this->components = $cache->getData() ) ) {

            /**
             * Add modules of which source codes are in `app/modules` as Psr4 packages
             */
            foreach ( $composerLoader->getFallbackDirsPsr4() as $dir ) {
                foreach ( File::getFolders( $dir ) as $vendor ) {
                    foreach ( File::getFolders( $dir . '/' . $vendor ) as $module ) {
                        $prefix = $vendor . '\\' . $module . '\\';
                        $path = $dir . '/' . $vendor . '/' . $module;
                        if ( is_file( $path . DS . 'registration.php' ) ) {
                            require $path . DS . 'registration.php';
                            $composerLoader->addPsr4( $prefix, $path . DS . 'code' );
                        }
                    }
                }
            }

            /**
             * Store in cache
             */
            $cache->setData( $this->components )->save();
        }

        return $this->components;
    }

    /**
     * @param string $name
     * @param string $type
     * @param string $dir
     */
    public function register( $name, $type, $dir )
    {
        if ( !isset( $this->components[$type] ) ) {
            throw new \Exception( sprintf( 'Component type `%s` does not exist.', $type ) );
        }
        if ( isset( $this->components[$type][$name] ) ) {
            throw new \Exception( sprintf( 'Component name `%s` has been used.', $name ) );
        }
        $this->components[$type][$name] = [
            'dir' => $dir,
            'name' => $name
        ];
    }

}
