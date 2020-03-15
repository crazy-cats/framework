<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Theme;

use CrazyCat\Framework\App\Config;
use CrazyCat\Framework\App\Io\Http\Request;
use CrazyCat\Framework\App\Module\Manager as ModuleManager;
use CrazyCat\Framework\App\ObjectManager;
use CrazyCat\Framework\App\Theme;
use CrazyCat\Framework\App\Url;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     http://crazy-cat.cn
 */
class Page extends \CrazyCat\Framework\Data\DataObject {

    /**
     * @var \CrazyCat\Framework\App\Config
     */
    private $config;

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

    /**
     * @var array
     */
    private $cssInfo = [ 'files' => [], 'links' => [] ];

    public function __construct( Config $config, Url $url, ModuleManager $moduleManager, ObjectManager $objectManager, Request $request, Theme $theme )
    {
        parent::__construct();

        $this->config = $config;
        $this->moduleManager = $moduleManager;
        $this->objectManager = $objectManager;
        $this->request = $request;
        $this->theme = $theme;
        $this->url = $url;
    }

    /**
     * Layout B cover layout A
     * 
     * @param array $layoutA
     * @param array $layoutB
     * @return array
     */
    private function mergeLayoutBlocks( array $layoutA, array $layoutB )
    {
        foreach ( $layoutA as $sectionName => &$blockGroup ) {
            if ( isset( $layoutB[$sectionName] ) ) {
                $blockGroup = array_merge( $blockGroup, $layoutB[$sectionName] );
                unset( $layoutB[$sectionName] );
            }
        }
        return array_merge( $layoutA, $layoutB );
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

        $cssA = empty( $layoutA['css'] ) ? [] : $layoutA['css'];
        $cssB = empty( $layoutB['css'] ) ? [] : $layoutB['css'];

        return [
            'template' => empty( $layoutB['template'] ) ? ( empty( $layoutA['template'] ) ? '1column' : $layoutA['template'] ) : $layoutB['template'],
            'css' => array_merge( $cssA, $cssB ),
            'blocks' => $this->mergeLayoutBlocks( $blocksA, $blocksB )
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
            foreach ( $blocks as $blockName => $blockInfo ) {
                $data = isset( $blockInfo['data'] ) ? $blockInfo['data'] : [];
                $data['name'] = $blockName;
                $this->sectionsHtml[$sectionName] .= $this->objectManager->create( $blockInfo['class'], [ 'data' => $data ] )->toHtml();
            }
        }
    }

    /**
     * @param array $cssLayout
     * @param boolean $merge
     * @return void
     */
    private function prepareCssScripts( array $cssLayout )
    {
        foreach ( $cssLayout as $css ) {
            $this->cssInfo['files'][] = $this->theme->getStaticPath( $css );
            $this->cssInfo['links'][] = $this->theme->getStaticUrl( $css );
        }
    }

    /**
     * @param string $layoutName
     * @param string $areaCode
     * @return \CrazyCat\Framework\App\Module|null
     */
    private function getLayoutModule( $layoutName, $areaCode )
    {
        foreach ( $this->moduleManager->getEnabledModules() as $module ) {
            if ( isset( $module['config']['routes'][$areaCode] ) ) {
                if ( strpos( $layoutName, $module['config']['routes'][$areaCode] . '_' ) === 0 ) {
                    return $module;
                }
            }
        }
    }

    /**
     * @param string $layoutName
     * @return array
     */
    public function getLayoutFromFile( $layoutName )
    {
        $layoutFile = $this->theme->getData( 'dir' ) . DS . 'view' . DS . 'layouts' . DS . $layoutName . '.php';
        if ( is_file( $layoutFile ) && ( $layout = require $layoutFile ) && is_array( $layout ) ) {
            return $layout;
        }

        $areaCode = $this->theme->getData( 'config' )['area'];
        if ( ( $module = $this->getLayoutModule( $layoutName, $areaCode ) ) ) {
            $layoutFile = $module->getData( 'dir' ) . DS . 'view' . DS . $areaCode . DS . 'layouts' . DS . $layoutName . '.php';
            if ( is_file( $layoutFile ) && ( $layout = require $layoutFile ) && is_array( $layout ) ) {
                return $layout;
            }
        }

        return [];
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
        return $this->theme->getStaticUrl( $path );
    }

    /**
     * @param string $separator
     * @return string
     */
    public function getLayoutName( $separator = '_' )
    {
        return $this->request->getFullPath( $separator );
    }

    /**
     * @return string
     */
    public function getCssScripts()
    {
        if ( $this->config->getData( $this->theme->getData( 'config' )['area'] )['merge_css'] ) {
            return '';
        }
        else {
            $scripts = '';
            foreach ( $this->cssInfo['links'] as $cssLink ) {
                $scripts .= '<link rel="stylesheet" type="text/css" media="all" href="' . $cssLink . "\" />\n";
            }
            return $scripts;
        }
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
        $this->prepareCssScripts( $layout['css'] );
        $this->prepareBlocks( $layout['blocks'] );

        ob_start();
        $templateFile = $this->theme->getData( 'dir' ) . DS . 'view/templates/pages' . DS . $layout['template'] . '.php';
        if ( is_file( $templateFile ) ) {
            include $templateFile;
        }
        else {
            throw new \Exception( sprintf( 'Template file `%s` does not exist.', $templateFile ) );
        }
        return ob_get_clean();
    }

}
