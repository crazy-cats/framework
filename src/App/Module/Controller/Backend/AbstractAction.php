<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Module\Controller\Backend;

use CrazyCat\Framework\App\Area;
use CrazyCat\Framework\App\EventManager;
use CrazyCat\Framework\App\Io\Http\Request;
use CrazyCat\Framework\App\ObjectManager;
use CrazyCat\Framework\App\Session\Backend as Session;
use CrazyCat\Framework\App\Session\Messenger;
use CrazyCat\Framework\App\Theme\Manager as ThemeManager;
use CrazyCat\Framework\App\Url;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
abstract class AbstractAction extends \CrazyCat\Framework\App\Module\Controller\AbstractViewAction {

    /**
     * @var \CrazyCat\Framework\App\Session\Backend
     */
    protected $session;

    public function __construct( Session $session, Url $url, Messenger $messenger, ThemeManager $themeManager, Request $request, Area $area, EventManager $eventManager, ObjectManager $objectManager )
    {
        parent::__construct( $url, $messenger, $themeManager, $request, $area, $eventManager, $objectManager );

        $this->session = $session;
    }

}
