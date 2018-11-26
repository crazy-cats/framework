<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Module\Controller;

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
     * @var array|null
     */
    protected $layout;

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

    public function __construct( Url $url, Messenger $messenger, ThemeManager $themeManager, Request $request, EventManager $eventManager, ObjectManager $objectManager )
    {
        parent::__construct( $eventManager, $objectManager );

        $this->request = $request;
        $this->response = $request->getResponse();
        $this->messenger = $messenger;
        $this->themeManager = $themeManager;
        $this->url = $url;
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
        $this->response->setType( Response::TYPE_PAGE )->setBody( $page->toHtml() );
    }

    /**
     * @param string $path
     * @param array $params
     * @return void
     */
    protected function redirect( $path, $params = [] )
    {
        $this->response->setType( Response::TYPE_REDIRECT )->setData( $this->url->getUrl( $path, $params ) );
    }

    /**
     * @return void
     */
    public function execute()
    {
        $this->eventManager->dispatch( 'controller_execute_before', [ 'action' => $this ] );
        $this->themeManager->init();
        $this->run();
        $this->response->send();
    }

    /**
     * @return void
     */
    abstract protected function run();
}
