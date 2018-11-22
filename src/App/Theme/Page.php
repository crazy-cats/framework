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
use CrazyCat\Framework\App\Url;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class Page extends \CrazyCat\Framework\Data\Object {

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
     * @var \CrazyCat\Framework\App\Url
     */
    private $url;

    /**
     * @var array|null
     */
    private $layout;

    /**
     * @var string[]
     */
    private $sectionsHtml;

    public function __construct( Url $url, ModuleManager $moduleManager, ObjectManager $objectManager, Request $request, Theme $theme )
    {
        parent::__construct();

        $this->moduleManager = $moduleManager;
        $this->objectManager = $objectManager;
        $this->request = $request;
        $this->theme = $theme;
        $this->url = $url;
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
        $blocksA = empty( $layoutA['blocks'] ) ? [] : $layoutA['blocks'];
        $blocksB = empty( $layoutB['blocks'] ) ? [] : $layoutB['blocks'];

        return [
            'template' => empty( $layoutB['template'] ) ? ( empty( $layoutA['template'] ) ? '1column' : $layoutA['template'] ) : $layoutB['template'],
            'blocks' => array_merge_recursive( $blocksA, $blocksB )
        ];
    }

    /**
     * @param array $blocksLayout
     * @return void
     */
    private function prepareBlocks( array $blocksLayout )
    {
        foreach ( $blocksLayout as $sectionName => $blocks ) {
            $this->sectionsHtml[$sectionName] = '';
            foreach ( $blocks as $blockInfo ) {
                $this->sectionsHtml[$sectionName] .= $this->objectManager->create( $blockInfo['class'], [ 'data' => isset( $blockInfo['data'] ) ? $blockInfo['data'] : [] ] )->toHtml();
            }
        }
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
     * @param string $sectionName
     * @return string
     */
    public function getSectionHtml( $sectionName )
    {
        return isset( $this->sectionsHtml[$sectionName] ) ? $this->sectionsHtml[$sectionName] : '';
    }

    /**
     * @param string $path
     * @return string
     */
    public function getThemeUrl( $path )
    {
        return $path;
    }

    /**
     * @return string
     */
    public function getLayoutName( $separator = '_' )
    {
        return $this->request->getRouteName() .
                $separator .
                $this->request->getControllerName() .
                $separator .
                $this->request->getActionName();
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        $layout = $this->mergeLayout( $this->getLayoutFromFile( 'default' ), $this->getLayoutFromFile( $this->getLayoutName() ) );
        if ( $this->layout !== null ) {
            $layout = $this->mergeLayout( $layout, $this->layout );
        }
        $this->prepareBlocks( $layout['blocks'] );

        ob_start();
        $templateFile = $this->theme->getData( 'dir' ) . DS . 'view' . DS . 'templates' . DS . $layout['template'] . '.php';
        if ( is_file( $templateFile ) ) {
            include $templateFile;
        }
        return ob_get_clean();
    }

}
