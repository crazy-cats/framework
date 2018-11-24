<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Setup;

use CrazyCat\Framework\App\Area;
use CrazyCat\Framework\App\Config;
use CrazyCat\Framework\App\ObjectManager;
use CrazyCat\Framework\App\Setup\Component;
use CrazyCat\Framework\Data\Object;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class Wizard {

    /**
     * @var \CrazyCat\Framework\App\Area
     */
    private $area;

    public function __construct( Area $area )
    {
        $this->area = $area;
    }

    /**
     * @param array|string $settings
     * @param string $path
     */
    private function getInputSettings( &$settings, $path = '' )
    {
        if ( is_array( $settings ) ) {
            foreach ( $settings as $field => &$childSettings ) {
                $this->getInputSettings( $childSettings, $path ? ( $path . '/' . $field ) : $field  );
            }
        }
        elseif ( $settings === null ) {
            echo sprintf( 'Please set %s: ', $path );

            $input = fgets( STDIN );
            if ( ( $input = trim( $input ) ) != '' ) {
                $settings = $input;
            }
            else {
                $this->getInputSettings( $settings, $path );
            }
        }
    }

    /**
     * @return void
     */
    private function setupFromCli()
    {
        echo "\nFollow the wizard to complete minimum configuration which will store at `app/config/env.php`.\n\n";

        $envSettins = [
            'global' => [
                'cache' => [
                    'type' => 'file',
                ],
                'session' => [
                    'type' => 'file',
                ],
                'db' => [
                    'default' => [
                        'type' => 'mysql',
                        'host' => null,
                        'username' => null,
                        'password' => null,
                        'database' => null,
                        'prefix' => ''
                    ]
                ]
            ],
            'api' => [
                'token' => md5( date( 'Y-m-d H:i:s' ) . rand( 1000, 9999 ) )
            ],
            'backend' => [
                'route' => null,
                'theme' => 'default'
            ],
            'frontend' => [
                'theme' => 'default'
            ]
        ];
        $this->getInputSettings( $envSettins );
        file_put_contents( Config::FILE, sprintf( "<?php\nreturn %s;", ( new Object )->toString( $envSettins ) ) );

        echo "\n";
    }

    /**
     * @return void
     */
    private function setupFromHttp()
    {
        exit( 'Please run `index.php` in CLI to complete the setup.' );
    }

    /**
     * @return void
     */
    public function launch()
    {
        foreach ( [ Config::DIR, Component::DIR_APP_MODULES, Component::DIR_APP_THEMES ] as $dir ) {
            if ( !is_dir( $dir ) ) {
                mkdir( $dir, 0755, true );
            }
        }

        if ( $this->area->isCli() ) {
            $this->setupFromCli();
        }
        else {
            $this->setupFromHttp();
        }
    }

    /**
     * This method is run on `post-create-project-cmd` event which
     *      dispatched after composer `create-project` command executed.
     * 
     * @return void
     */
    static public function install()
    {
        require 'definitions';

        ObjectManager::getInstance()->get( self::class )->launch();
    }

}
