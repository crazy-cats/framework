<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Setup;

use CrazyCat\Framework\App\Area;
use CrazyCat\Framework\App\Config;
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
    private $cliInput;

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

            $input = fgets( $this->cliInput );
            if ( ( $input = trim( $input ) ) != '' ) {
                $settings = $input;
            }
            else {
                $this->getInputSettings( $settings, $path );
            }
        }
    }

    private function setupFromCli()
    {
        $this->cliInput = fopen( 'php://stdin', 'r' );

        $envSettins = [
            'global' => [
                'cache' => 'file',
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
            'backend' => [
                'route' => null
            ]
        ];
        $this->getInputSettings( $envSettins );

        fclose( $this->cliInput );

        file_put_contents( Config::FILE, sprintf( "<?php\nreturn %s;", ( new Object )->toString( $envSettins ) ) );
    }

    private function setupFromHttp()
    {
        die( 'Hello world' );
    }

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

}
