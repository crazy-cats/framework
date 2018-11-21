<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Module\Controller\Api;

use CrazyCat\Framework\App\EventManager;
use CrazyCat\Framework\App\Io\Http\Request;
use CrazyCat\Framework\App\ObjectManager;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
abstract class AbstractAction extends \CrazyCat\Framework\App\Module\Controller\AbstractAction {

    /**
     * @var \CrazyCat\Framework\App\Io\Http\Request
     */
    protected $request;

    public function __construct( Request $request, EventManager $eventManager, ObjectManager $objectManager )
    {
        parent::__construct( $eventManager, $objectManager );

        $this->request = $request;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $this->eventManager->dispatch( 'controller_execute_before', [ 'action' => $this ] );

        if ( !( $auth = $this->request->getHeader( 'Authorization' ) ) ) {
            throw new \Exception( 'You do not have permission to access the resource.' );
        }
        $verifyObj = new Object( [ 'token_validated' => false ] );
        foreach ( preg_split( '/\s*,\s*/', $auth ) as $authStr ) {
            list( $type, $token ) = preg_split( '/\s+/', $authStr );
            if ( $type == 'Bearer' ) {
                $this->eventManager->dispatch( 'verify_api_token', [ 'token' => $token, 'verify_object' => $verifyObj ] );
                break;
            }
        }
        if ( !$verifyObj->getData( 'token_validated' ) ) {
            throw new \Exception( 'You do not have permission to access the resource.' );
        }

        $this->run();
        $this->request->getResponse()->setType( Response::TYPE_JSON )->send();
    }

    /**
     * @return void
     */
    abstract protected function run();
}
