<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Component\Module\Controller\Backend;

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
        \CrazyCat\Framework\App\Io\Http\Request $request,
        \CrazyCat\Framework\App\Io\Http\Session\Backend $session,
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
            $request,
            $session,
            $messenger,
            $url,
            $logger,
            $objectManager,
            $registry
        );
    }
}
