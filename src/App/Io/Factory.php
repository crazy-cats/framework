<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Io;

use CrazyCat\Framework\App\Area;
use CrazyCat\Framework\App\ObjectManager;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class Factory {

    /**
     * @var \CrazyCat\Framework\App\Area
     */
    private $area;

    /**
     * @var \CrazyCat\Framework\App\ObjectManager
     */
    private $objectManager;

    public function __construct( Area $area, ObjectManager $objectManager )
    {
        $this->area = $area;
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $areaCode
     * @return \CrazyCat\Framework\App\Io\AbstractRequest
     */
    public function create( $areaCode = null )
    {
        if ( $areaCode === null ) {
            if ( !$this->area->isCli() ) {
                $request = $this->objectManager->create( Http\Request::class );
            }
            else {
                $request = $this->objectManager->create( Cli\Request::class );
            }
        }
        else {
            switch ( $areaCode ) {

                case Area::CODE_API :
                case Area::CODE_BACKEND :
                case Area::CODE_FRONTEND :
                    $request = $this->objectManager->create( Http\Request::class );
                    break;

                case Area::CODE_CLI :
                    $request = $this->objectManager->create( Cli\Request::class );
                    break;
            }
        }

        return $request;
    }

}
