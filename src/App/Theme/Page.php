<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Theme;

use CrazyCat\Framework\App\Io\Http\Request;
use CrazyCat\Framework\App\Module\Manager as ModuleManager;
use CrazyCat\Framework\App\ObjectManager;
use CrazyCat\Framework\App\Theme;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class Page {

    /**
     * @var \CrazyCat\Framework\App\Module\Manager
     */
    private $moduleManager;

    /**
     * @var \CrazyCat\Framework\App\ObjectManager
     */
    private $objectManager;

    /**
     * @var \CrazyCat\Framework\App\Io\Http\Request
     */
    private $request;

    /**
     * @var \CrazyCat\Framework\App\Theme
     */
    private $theme;

    /**
     * @var array|null
     */
    protected $layout;

    public function __construct( ModuleManager $moduleManager, ObjectManager $objectManager, Request $request, Theme $theme )
    {
        $this->moduleManager = $moduleManager;
        $this->objectManager = $objectManager;
        $this->request = $request;
        $this->theme = $theme;
    }

    /**
     * @param string $layoutName
     * @return array
     */
    private function getLayoutFromFile( $layoutName )
    {
        $layoutFile = $this->theme->getData( 'dir' ) . DS . 'view' . DS . 'layouts' . DS . $layoutName . '.php';
        if ( is_file( $layoutFile ) && ( $layout = require $layoutFile ) && is_array( $layout ) ) {
            return $layout;
        }

        list( $routeName ) = explode( '_', $layoutName );
        $areaCode = $this->theme->getData( 'config' )['area'];
        if ( ( $module = $this->moduleManager->getModuleByRoute( $routeName, $areaCode ) ) ) {
            $layoutFile = $module->getData( 'dir' ) . DS . 'view' . DS . $areaCode . DS . 'layouts' . DS . $layoutName . '.php';
            if ( is_file( $layoutFile ) && ( $layout = require $layoutFile ) && is_array( $layout ) ) {
                return $layout;
            }
        }

        return [];
    }

    /**
     * Layout B cover layout A
     * 
     * @param array $layoutA
     * @param array $layoutB
     * @return array
     */
    private function mergeLayout( array $layoutA, array $layoutB )
    {
        return array_merge( $layoutA, $layoutB );
    }

    /**
     * @param array $layout
     * @return $this
     */
    public function setLayout( array $layout )
    {
        $this->layout = $layout;
        return $this;
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        $layoutName = sprintf( '%s_%s_%s', $this->request->getRouteName(), $this->request->getControllerName(), $this->request->getActionName() );
        $layout = $this->mergeLayout( $this->getLayoutFromFile( 'default' ), $this->getLayoutFromFile( $layoutName ) );

        return '';
    }

}
