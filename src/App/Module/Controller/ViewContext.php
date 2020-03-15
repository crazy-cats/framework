<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Module\Controller;

use CrazyCat\Framework\App\Area;
use CrazyCat\Framework\App\Config;
use CrazyCat\Framework\App\Cookies;
use CrazyCat\Framework\App\EventManager;
use CrazyCat\Framework\App\Logger;
use CrazyCat\Framework\App\Io\Http\Request;
use CrazyCat\Framework\App\ObjectManager;
use CrazyCat\Framework\App\Registry;
use CrazyCat\Framework\App\Session\Messenger;
use CrazyCat\Framework\App\Theme\Manager as ThemeManager;
use CrazyCat\Framework\App\Translator;
use CrazyCat\Framework\App\Url;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     http://crazy-cat.cn
 */
class ViewContext extends Context {

    /**
     * @var \CrazyCat\Framework\App\Cookies
     */
    protected $cookies;

    /**
     * @var \CrazyCat\Framework\App\Session\Messenger
     */
    protected $messenger;

    /**
     * @var \CrazyCat\Framework\App\Io\Http\Request
     */
    protected $request;

    /**
     * @var \CrazyCat\Framework\App\Io\Http\Response
     */
    protected $response;

    /**
     * @var \CrazyCat\Framework\App\Registry
     */
    protected $registry;

    /**
     * @var \CrazyCat\Framework\App\Theme\Manager
     */
    protected $themeManager;

    /**
     * @var \CrazyCat\Framework\App\Translator
     */
    protected $translator;

    /**
     * @var \CrazyCat\Framework\App\Url
     */
    protected $url;

    public function __construct( Translator $translator, Cookies $cookies, Registry $registry, Url $url, Messenger $messenger, ThemeManager $themeManager, Request $request, Area $area, Config $config, Logger $logger, EventManager $eventManager, ObjectManager $objectManager )
    {
        parent::__construct( $area, $config, $logger, $eventManager, $objectManager );

        $this->cookies = $cookies;
        $this->messenger = $messenger;
        $this->request = $request;
        $this->response = $request->getResponse();
        $this->registry = $registry;
        $this->themeManager = $themeManager;
        $this->translator = $translator;
        $this->url = $url;
    }

    /**
     * @return \CrazyCat\Framework\App\Cookies
     */
    public function getCookies()
    {
        return $this->cookies;
    }

    /**
     * @return \CrazyCat\Framework\App\Session\Messenger
     */
    public function getMessenger()
    {
        return $this->messenger;
    }

    /**
     * @return \CrazyCat\Framework\App\Io\Http\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return \CrazyCat\Framework\App\Io\Http\Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return \CrazyCat\Framework\App\Registry
     */
    public function getRegistry()
    {
        return $this->registry;
    }

    /**
     * @return \CrazyCat\Framework\App\Theme\Manager
     */
    public function getThemeManager()
    {
        return $this->themeManager;
    }

    /**
     * @return \CrazyCat\Framework\App\Translator
     */
    public function getTranslator()
    {
        return $this->translator;
    }

    /**
     * @return \CrazyCat\Framework\App\Url
     */
    public function getUrl()
    {
        return $this->url;
    }

}
