<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Setup;

use CrazyCat\Framework\App\Module;
use CrazyCat\Framework\App\ObjectManager;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class Component {

    const TYPE_LANG = 'lang';
    const TYPE_MODULE = 'module';
    const TYPE_THEME = 'theme';

    private $components = [
        self::TYPE_LANG => [],
        self::TYPE_MODULE => [],
        self::TYPE_THEME => []
    ];

    /**
     * Get component setup singleton
     * @return \CrazyCat\Framework\App\Setup\Component
     */
    static public function getInstance()
    {
        return ObjectManager::getInstance()->get( self::class );
    }

    /**
     * @return array|null
     */
    private function getComponentConfig( $configFile )
    {
        return is_file( $configFile ) ? json_decode( file_get_contents( $configFile ), true ) : null;
    }

    /**
     * @param \Composer\Autoload\ClassLoader $composerLoader
     * @param boolean $forceGenerate
     * @see https://getcomposer.org/apidoc/master/Composer/Autoload/ClassLoader.html
     */
    public function init( $composerLoader, $rootDir, $forceGenerate = false )
    {
        $configFile = $rootDir . '/app/config/components.json';

        if ( $forceGenerate || !( $componentConfig = $this->getComponentConfig( $configFile ) ) ) {
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
             * Create component config file
             */
            if ( !is_dir( $rootDir . '/app/config' ) ) {
                mkdir( $rootDir . '/app/config', 0755, true );
            }
            file_put_contents( $configFile, json_encode( $this->components ) );

            return $this->components;
        }

        return $componentConfig;
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
        $this->components[$type][$name] = ObjectManager::getInstance()->create( Module::class, [
            'name' => $name,
            'dir' => $dir ] );
    }

}
