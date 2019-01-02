<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Handler;

use CrazyCat\Framework\App\Area;
use CrazyCat\Framework\App\Io\Http\Request as HttpRequest;
use CrazyCat\Framework\App\Io\Http\Response as HttpResponse;
use CrazyCat\Framework\App\Logger;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class ExceptionHandler {

    /**
     * @var \CrazyCat\Framework\App\Area
     */
    private $area;

    /**
     * @var \CrazyCat\Framework\App\Io\Http\Request
     */
    private $httpRequest;

    /**
     * @var \CrazyCat\Framework\App\Io\Http\Response
     */
    private $httpResponse;

    /**
     * @var \CrazyCat\Framework\App\Logger
     */
    private $logger;

    public function __construct( HttpRequest $httpRequest, HttpResponse $httpResponse, Area $area, Logger $logger )
    {
        $this->area = $area;
        $this->httpRequest = $httpRequest;
        $this->httpResponse = $httpResponse;
        $this->logger = $logger;
    }

    /**
     * @param string $message
     * @return string
     */
    private function logException( $message )
    {
        $this->logger->log( $message, sprintf( 'errors/%s/%s.log', date( 'Y-m' ), date( 'Y-m-d' ) ) );

        return $message;
    }

    /**
     * @param \Exception $exception
     */
    private function processCliException( $exception )
    {
        echo $this->logException( $exception->getMessage() . "\n" . $exception->getTraceAsString() );
    }

    /**
     * @param \Exception $exception
     */
    private function processHttpException( $exception )
    {
        if ( $this->area->getCode() == Area::CODE_API || $this->httpRequest->getParam( HttpRequest::AJAX_PARAM ) ) {
            $this->httpResponse->setType( HttpResponse::TYPE_JSON )
                    ->setData( [ 'error' => true, 'message' => $exception->getMessage(), 'trace' => $exception->getTraceAsString() ] )
                    ->send();
            exit;
        }
        else {
            echo sprintf( '<pre>%s</pre>', $this->logException( $exception->getMessage() . "\n" . $exception->getTraceAsString() ) );
        }
    }

    /**
     * @param \Exception $exception
     */
    public function process( $exception )
    {
        return $this->area->isCli() ?
                $this->processCliException( $exception ) :
                $this->processHttpException( $exception );
    }

}
