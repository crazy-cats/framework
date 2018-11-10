<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Io\Http;

use CrazyCat\Framework\App\Area;
use CrazyCat\Framework\App\ObjectManager;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class Request extends \CrazyCat\Framework\App\Io\AbstractRequest {

    /**
     * @var \CrazyCat\Framework\App\Area
     */
    protected $area;

    /**
     * @var \CrazyCat\Framework\App\ObjectManager
     */
    protected $objectManager;

    public function __construct( Area $area, ObjectManager $objectManager )
    {
        $this->area = $area;
        $this->objectManager = $objectManager;
    }

    /**
     * @return \CrazyCat\Framework\App\Io\Http\Response
     */
    public function getResponse()
    {
        if ( $this->response === null ) {
            $this->response = $this->objectManager->create( Response::class );
        }
        return $this->response;
    }

    /**
     * @return void
     */
    public function process()
    {
        $response = $this->objectManager->create( Response::class );

        return $response;
    }

}
