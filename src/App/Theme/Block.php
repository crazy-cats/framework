<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Theme;

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
     * @var \CrazyCat\Framework\App\Cache\Factory
     */
    protected $cacheFactory;

    /**
     * @var \CrazyCat\Framework\App\EventManager
     */
    protected $eventManager;

    /**
     * @var \CrazyCat\Framework\App\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \CrazyCat\Framework\App\Registry
     */
    protected $registry;

    /**
     * @var \CrazyCat\Framework\App\Io\Http\Request
     */
    protected $request;

    /**
     * @var \CrazyCat\Framework\App\Theme\Manager
     */
    protected $themeManager;

    /**
     * @var \CrazyCat\Framework\App\Url
     */
    protected $url;

    /**
     * @var string
     */
    protected $template;

    public function __construct( Block\Context $context, array $data = [] )
    {
        parent::__construct( $data );

        $this->area = $context->getArea();
        $this->cacheFactory = $context->getCacheFactory();
        $this->eventManager = $context->getEventManager();
        $this->moduleManager = $context->getModuleManager();
        $this->registry = $context->getRegistry();
        $this->request = $context->getRequest();
        $this->themeManager = $context->getThemeManager();
        $this->url = $context->getUrl();

        if ( !empty( $data['template'] ) ) {
            $this->template = $data['template'];
        }

        $this->init();
    }

    /**
     * @return void
     */
    protected function init()
    {
        
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
     * @return \CrazyCat\Framework\App\Theme\Page
     */
    public function getPage()
    {
        return $this->themeManager->getCurrentTheme()->getPage();
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
