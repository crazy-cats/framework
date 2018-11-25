<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Module\Controller\Frontend;

use CrazyCat\Framework\App\EventManager;
use CrazyCat\Framework\App\Io\Http\Request;
use CrazyCat\Framework\App\ObjectManager;
use CrazyCat\Framework\App\Session\Frontend as Session;
use CrazyCat\Framework\App\Session\Messenger;
use CrazyCat\Framework\App\Theme\Manager as ThemeManager;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
abstract class AbstractAction extends \CrazyCat\Framework\App\Module\Controller\AbstractViewAction {

    /**
     * @var \CrazyCat\Framework\App\Session\Frontend
     */
    protected $session;

    public function __construct( Session $session, Messenger $messenger, ThemeManager $themeManager, Request $request, EventManager $eventManager, ObjectManager $objectManager )
    {
        parent::__construct( $messenger, $themeManager, $request, $eventManager, $objectManager );

        $this->session = $session;
    }

}
