<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Module\Controller;

use CrazyCat\Framework\App\Area;
use CrazyCat\Framework\App\EventManager;
use CrazyCat\Framework\App\Io\Http\Request;
use CrazyCat\Framework\App\Io\Http\Response;
use CrazyCat\Framework\App\ObjectManager;
use CrazyCat\Framework\App\Session\Messenger;
use CrazyCat\Framework\App\Theme\Manager as ThemeManager;
use CrazyCat\Framework\App\Url;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
abstract class AbstractViewAction extends AbstractAction {

    /**
     * @var \CrazyCat\Framework\App\Io\Http\Request
     */
    protected $request;

    /**
     * @var \CrazyCat\Framework\App\Io\Http\Response
     */
    protected $response;

    /**
     * @var \CrazyCat\Framework\App\Session\Messenger
     */
    protected $messenger;

    /**
     * @var \CrazyCat\Framework\App\Theme\Manager
     */
    protected $themeManager;

    /**
     * @var \CrazyCat\Framework\App\Url
     */
    protected $url;

    /**
     * @var array|null
     */
    protected $layout;

    /**
     * @var string|null
     */
    protected $pageTitle;

    /**
     * @var string|null
     */
    protected $metaKeywords;

    /**
     * @var string|null
     */
    protected $metaDescription;

    /**
     * @var string|null
     */
    protected $metaRobots;

    /**
     * @var boolean
     */
    protected $skipRunning = false;

    public function __construct( Url $url, Messenger $messenger, ThemeManager $themeManager, Request $request, Area $area, EventManager $eventManager, ObjectManager $objectManager )
    {
        parent::__construct( $area, $eventManager, $objectManager );

        $this->request = $request;
        $this->response = $request->getResponse();
        $this->messenger = $messenger;
        $this->themeManager = $themeManager;
        $this->url = $url;
    }

    /**
     * @param string $pageTitle
     * @return $this
     */
    protected function setPageTitle( $pageTitle )
    {
        $this->pageTitle = $pageTitle;
        return $this;
    }

    /**
     * @param array $metaKeywords
     * @return $this
     */
    protected function setMetaKeywords( array $metaKeywords )
    {
        $this->metaKeywords = implode( ', ', $metaKeywords );
        return $this;
    }

    /**
     * @param string $metaDescription
     * @return $this
     */
    protected function setMetaDescription( $metaDescription )
    {
        $this->metaDescription = $metaDescription;
        return $this;
    }

    /**
     * @param string $metaRobots
     * @return $this
     */
    protected function setMetaRobots( $metaRobots )
    {
        $this->metaRobots = $metaRobots;
        return $this;
    }

    /**
     * @param string $themeName
     * @return $this
     */
    protected function setTheme( $themeName )
    {
        $this->themeManager->setCurrentTheme( $themeName );
        return $this;
    }

    /**
     * @param array $layout
     * @return $this
     */
    protected function setLayout( array $layout )
    {
        $this->layout = $layout;
        return $this;
    }

    /**
     * @param string $layoutFile
     * @return $this
     */
    protected function setLayoutFile( $layoutFile )
    {
        $this->layout = $this->themeManager->getCurrentTheme()->getPage()->getLayoutFromFile( $layoutFile );
        return $this;
    }

    /**
     * @return void
     */
    protected function render()
    {
        $page = $this->themeManager->getCurrentTheme()->getPage();
        if ( $this->layout !== null ) {
            $page->setLayout( $this->layout );
        }
        if ( $this->pageTitle !== null ) {
            $page->setData( 'page_title', $this->pageTitle );
        }
        if ( $this->metaKeywords !== null ) {
            $page->setData( 'meta_keywords', $this->metaKeywords );
        }
        if ( $this->metaDescription !== null ) {
            $page->setData( 'meta_description', $this->metaDescription );
        }
        if ( $this->metaRobots !== null ) {
            $page->setData( 'meta_robots', $this->metaRobots );
        }
        $this->response->setType( Response::TYPE_PAGE )->setBody( $page->toHtml() );
    }

    /**
     * @param string $path
     * @param array $params
     * @return void
     */
    public function redirect( $path, $params = [] )
    {
        $this->response->setType( Response::TYPE_REDIRECT )->setData( $this->url->getUrl( $path, $params ) );
    }

    /**
     * @return $this
     */
    public function skipRunning()
    {
        $this->skipRunning = true;
        return $this;
    }

    /**
     * @return void
     */
    public function execute()
    {
        parent::execute();

        if ( !$this->skipRunning ) {
            $this->themeManager->init();
            $this->run();
        }

        $this->response->send();
    }

    /**
     * @return void
     */
    abstract protected function run();
}
