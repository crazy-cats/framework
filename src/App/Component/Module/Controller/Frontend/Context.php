<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Component\Module\Controller\Frontend;

use CrazyCat\Framework\App\Area;
use CrazyCat\Framework\App\Config;
use CrazyCat\Framework\App\Io\Http\Cookies;
use CrazyCat\Framework\App\EventManager;
use CrazyCat\Framework\App\Logger;
use CrazyCat\Framework\App\Io\Http\Request;
use CrazyCat\Framework\App\ObjectManager;
use CrazyCat\Framework\App\Registry;
use CrazyCat\Framework\App\Io\Http\Session\Frontend as Session;
use CrazyCat\Framework\App\Io\Http\Session\Messenger;
use CrazyCat\Framework\App\Component\Theme\Manager as ThemeManager;
use CrazyCat\Framework\App\Component\Language\Translator;
use CrazyCat\Framework\App\Url;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     http://crazy-cat.cn
 */
class Context extends \CrazyCat\Framework\App\Component\Module\Controller\ViewContext {

    /**
     * @var \CrazyCat\Framework\App\Io\Http\Session\Backend
     */
    protected $session;

    public function __construct( Session $session, Translator $translator, Cookies $cookies, Registry $registry, Url $url, Messenger $messenger, ThemeManager $themeManager, Request $request, Area $area, Config $config, Logger $logger, EventManager $eventManager, ObjectManager $objectManager )
    {
        parent::__construct( $translator, $cookies, $registry, $url, $messenger, $themeManager, $request, $area, $config, $logger, $eventManager, $objectManager );

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
