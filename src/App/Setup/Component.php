<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Setup;

use CrazyCat\Framework\App\Cache\Factory as CacheFactory;
use CrazyCat\Framework\App\ObjectManager;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class Component {

    const CACHE_NAME = 'component';

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
            $funGetSubDirs = function( $dir ) {
                $subDirs = [];
                if ( ( $dh = opendir( $dir ) ) ) {
                    while ( ( $file = readdir( $dh ) ) !== false ) {
                        if ( $file !== '.' && $file !== '..' && is_dir( $dir . '/' . $file ) ) {
                            $subDirs[] = $file;
                        }
                    }
                    closedir( $dh );
                }
                return $subDirs;
            };
            foreach ( $composerLoader->getFallbackDirsPsr4() as $dir ) {
                foreach ( $funGetSubDirs( $dir ) as $vendor ) {
                    foreach ( $funGetSubDirs( $dir . '/' . $vendor ) as $module ) {
                        $prefix = $vendor . '\\' . $module . '\\';
                        $path = $dir . '/' . $vendor . '/' . $module . '/code';
                        $composerLoader->addPsr4( $prefix, $path );
                    }
                }
            }

            /**
             * Collect components
             */
            foreach ( $composerLoader->getPrefixesPsr4() as $dirs ) {
                foreach ( $dirs as $dir ) {
                    if ( is_file( $dir . '/../registration.php' ) ) {
                        require $dir . '/../registration.php';
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
            'dir' => $dir
        ];
    }

}
