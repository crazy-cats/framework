<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Module\Controller\Backend;

use CrazyCat\Framework\App\Area;
use CrazyCat\Framework\App\Config;
use CrazyCat\Framework\App\Cookies;
use CrazyCat\Framework\App\EventManager;
use CrazyCat\Framework\App\Logger;
use CrazyCat\Framework\App\Io\Http\Request;
use CrazyCat\Framework\App\ObjectManager;
use CrazyCat\Framework\App\Session\Backend as Session;
use CrazyCat\Framework\App\Session\Messenger;
use CrazyCat\Framework\App\Theme\Manager as ThemeManager;
use CrazyCat\Framework\App\Translator;
use CrazyCat\Framework\App\Url;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class Context extends \CrazyCat\Framework\App\Module\Controller\ViewContext {

    /**
     * @var \CrazyCat\Framework\App\Session\Backend
     */
    protected $session;

    public function __construct( Session $session, Translator $translator, Cookies $cookies, Url $url, Messenger $messenger, ThemeManager $themeManager, Request $request, Area $area, Config $config, Logger $logger, EventManager $eventManager, ObjectManager $objectManager )
    {
        parent::__construct( $translator, $cookies, $url, $messenger, $themeManager, $request, $area, $config, $logger, $eventManager, $objectManager );

        $this->session = $session;
    }

    /**
     * @return \CrazyCat\Framework\App\Io\Http\Response
     */
    public function getSession()
    {
        return $this->session;
    }

}
