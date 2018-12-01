<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Module\Controller\Api;

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

    /**
     * @var \CrazyCat\Framework\App\Io\Http\Response
     */
    protected $response;

    public function __construct( Context $context )
    {
        parent::__construct( $context );

        $this->request = $context->getRequest();
        $this->response = $context->getResponse();
    }

    /**
     * @return void
     */
    public function execute()
    {
        parent::execute();

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
        $this->response->setType( Response::TYPE_JSON )->send();
    }

    /**
     * @return void
     */
    abstract protected function run();
}
