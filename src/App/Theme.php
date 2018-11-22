<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App;

use CrazyCat\Framework\App\Theme\Page;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class Theme extends \CrazyCat\Framework\Data\Object {

    const FILE_CONFIG = 'config' . DS . 'theme.php';

    /**
     * @var array
     */
    private $configRules = [
        'area' => [ 'required' => true, 'type' => 'string' ],
        'alias' => [ 'required' => true, 'type' => 'string' ]
    ];

    /**
     * @var \CrazyCat\Framework\App\ObjectManager
     */
    private $objectManager;

    /**
     * @var \CrazyCat\Framework\App\Theme\Page
     */
    private $page;

    public function __construct( ObjectManager $objectManager, array $data )
    {
        parent::__construct( $this->init( $data ) );

        $this->objectManager = $objectManager;
    }

    /**
     * @param array $data
     * @return array
     */
    private function verifyConfig( $data )
    {
        if ( !is_file( $data['dir'] . DS . self::FILE_CONFIG ) ) {
            throw new \Exception( sprintf( 'Config file of theme `%s` does not exist.', $data['name'] ) );
        }
        $config = require $data['dir'] . DS . self::FILE_CONFIG;

        if ( !is_array( $config ) ) {
            throw new \Exception( sprintf( 'Invalidated config file of theme `%s`.', $data['name'] ) );
        }
        foreach ( $config as $key => $value ) {
            if ( !isset( $this->configRules[$key] ) ) {
                unset( $config[$key] );
            }
            elseif ( gettype( $value ) != $this->configRules[$key]['type'] ) {
                throw new \Exception( sprintf( 'Invalidated setting `%s` of theme `%s`.', $key, $data['name'] ) );
            }
        }
        foreach ( $this->configRules as $key => $rule ) {
            if ( $rule['required'] && !isset( $config[$key] ) ) {
                throw new \Exception( sprintf( 'Setting `%s` of theme `%s` is required.', $key, $data['name'] ) );
            }
        }
        if ( !in_array( $config['area'], [ Area::CODE_FRONTEND, Area::CODE_BACKEND ] ) ) {
            throw new \Exception( sprintf( 'Invalidated area of theme `%s`.', $key, $data['name'] ) );
        }
        return $config;
    }

    /**
     * @param array $data
     * @return array
     */
    private function init( $data )
    {
        /**
         * Consider the theme data is got from cache and skip
         *     initializing actions when it is with `config`.
         */
        if ( !isset( $data['config'] ) ) {
            $data['config'] = $this->verifyConfig( $data );

            /**
             * Use alias as theme name, because the unique component
             *     name does not make sence for a theme.
             */
            $data['name'] = $data['config']['alias'];
        }

        return $data;
    }

    /**
     * @var \CrazyCat\Framework\App\Theme\Page
     */
    public function getPage()
    {
        if ( $this->page === null ) {
            $this->page = $this->objectManager->create( Page::class, [ 'theme' => $this ] );
        }
        return $this->page;
    }

}
