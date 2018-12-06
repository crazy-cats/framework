<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Module\Controller\Api;

use CrazyCat\Framework\App\Area;
use CrazyCat\Framework\App\Config;
use CrazyCat\Framework\App\EventManager;
use CrazyCat\Framework\App\Logger;
use CrazyCat\Framework\App\Io\Http\Request;
use CrazyCat\Framework\App\ObjectManager;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class Context extends \CrazyCat\Framework\App\Module\Controller\Context {

    /**
     * @var \CrazyCat\Framework\App\Io\Http\Request
     */
    protected $request;

    /**
     * @var \CrazyCat\Framework\App\Io\Http\Response
     */
    protected $response;

    public function __construct( Request $request, Area $area, Config $config, Logger $logger, EventManager $eventManager, ObjectManager $objectManager )
    {
        parent::__construct( $area, $config, $logger, $eventManager, $objectManager );

        $this->request = $request;
        $this->response = $request->getResponse();
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

}
