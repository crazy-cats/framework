<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Theme;

use CrazyCat\Framework\App\Area;
use CrazyCat\Framework\App\EventManager;
use CrazyCat\Framework\App\Module\Manager as ModuleManager;
use CrazyCat\Framework\App\Theme\Manager as ThemeManager;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class Block extends \CrazyCat\Framework\Data\Object {

    /**
     * @var \CrazyCat\Framework\App\Area
     */
    protected $area;

    /**
     * @var \CrazyCat\Framework\App\EventManager
     */
    protected $eventManager;

    /**
     * @var \CrazyCat\Framework\App\Module\Manager
     */
    private $moduleManager;

    /**
     * @var \CrazyCat\Framework\App\Theme\Manager
     */
    protected $themeManager;

    /**
     * @var string
     */
    protected $template;

    public function __construct( Area $area, ModuleManager $moduleManager, ThemeManager $themeManager, EventManager $eventManager, array $data = [] )
    {
        parent::__construct( $data );

        $this->area = $area;
        $this->eventManager = $eventManager;
        $this->moduleManager = $moduleManager;
        $this->themeManager = $themeManager;

        if ( !empty( $data['template'] ) ) {
            $this->template = $data['template'];
        }
    }

    /**
     * @param string $template
     * @return string
     */
    protected function getAbsTemplatePath( $template )
    {
        list( $namespace, $filePath ) = explode( '::', $template );
        if ( is_file( $file = $this->themeManager->getCurrentTheme()->getData( 'dir' ) . DS . 'view/templates/blocks' . DS . str_replace( '\\', DS, $namespace ) . DS . $filePath . '.php' ) ) {
            return $file;
        }
        if ( ( $module = $this->moduleManager->getModule( $namespace ) ) ) {
            if ( is_file( $file = $module->getData( 'dir' ) . DS . 'view' . DS . $this->area->getCode() . DS . 'templates' . DS . $filePath . '.php' ) ) {
                return $file;
            }
        }
        throw new \Exception( sprintf( 'Block template file %s does not exist.', $template ) );
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        if ( empty( $this->template ) ) {
            return '';
        }

        ob_start();
        include $this->getAbsTemplatePath( $this->template );
        return ob_get_clean();
    }

}
