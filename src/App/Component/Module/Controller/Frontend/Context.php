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
use CrazyCat\Framework\App\Io\Http\Url;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     https://crazy-cat.cn
 */
class Context extends \CrazyCat\Framework\App\Component\Module\Controller\AbstractViewContext
{
    public function __construct(
        \CrazyCat\Framework\App\Area $area,
        \CrazyCat\Framework\App\Component\Language\Translator $translator,
        \CrazyCat\Framework\App\Component\Theme\Manager $themeManager,
        \CrazyCat\Framework\App\Config $config,
        \CrazyCat\Framework\App\EventManager $eventManager,
        \CrazyCat\Framework\App\Io\Http\Cookies $cookies,
        \CrazyCat\Framework\App\Io\Http\Session\Frontend $session,
        \CrazyCat\Framework\App\Io\Http\Session\Messenger $messenger,
        \CrazyCat\Framework\App\Io\Http\Url $url,
        \CrazyCat\Framework\App\Logger $logger,
        \CrazyCat\Framework\App\ObjectManager $objectManager,
        \CrazyCat\Framework\App\Registry $registry
    ) {
        parent::__construct(
            $area,
            $translator,
            $themeManager,
            $config,
            $eventManager,
            $cookies,
            $session,
            $messenger,
            $url,
            $logger,
            $objectManager,
            $registry
        );
    }
}
