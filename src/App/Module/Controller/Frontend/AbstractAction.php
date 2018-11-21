<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Module\Controller\Frontend;

use CrazyCat\Framework\App\EventManager;
use CrazyCat\Framework\App\Io\Http\Request;
use CrazyCat\Framework\App\Io\Http\Response;
use CrazyCat\Framework\App\ObjectManager;
use CrazyCat\Framework\App\Theme\Manager as ThemeManager;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
abstract class AbstractAction extends \CrazyCat\Framework\App\Module\Controller\AbstractAction {

    /**
     * @var \CrazyCat\Framework\App\Io\Http\Request
     */
    protected $request;

    /**
     * @var \CrazyCat\Framework\App\Io\Http\Response
     */
    protected $response;

    /**
     * @var \CrazyCat\Framework\App\Theme\Manager
     */
    protected $themeManager;

    public function __construct( ThemeManager $themeManager, Request $request, EventManager $eventManager, ObjectManager $objectManager )
    {
        parent::__construct( $eventManager, $objectManager );

        $this->request = $request;
        $this->response = $request->getResponse();
        $this->themeManager = $themeManager;
    }

    /**
     * @return \CrazyCat\Framework\App\Theme\Page
     */
    protected function getPage()
    {
        return $this->themeManager->getCurrentTheme()->getPage();
    }

    /**
     * @return void
     */
    protected function render()
    {
        $this->response->setType( Response::TYPE_PAGE )
                ->setBody( $this->getPage()->getHtml() );
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
