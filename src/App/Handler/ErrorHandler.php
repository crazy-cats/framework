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
class ErrorHandler {

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
    private function logError( $message )
    {
        $this->logger->log( $message, sprintf( 'errors/%s/%s.log', date( 'Y-m' ), date( 'Y-m-d' ) ) );

        return $message;
    }

    /**
     * @param string $errno
     * @param string $errstr
     * @param string $errfile
     * @param string $errline
     */
    private function processCliError( $errno, $errstr, $errfile, $errline )
    {
        echo $this->logError( sprintf( "\nMeet error on line %s of file %s:\n%s\n\n", $errline, $errfile, $errstr ) );
    }

    /**
     * @param string $errno
     * @param string $errstr
     * @param string $errfile
     * @param string $errline
     */
    private function processHttpError( $errno, $errstr, $errfile, $errline )
    {
        if ( $this->area->getCode() == Area::CODE_API || $this->httpRequest->getParam( HttpRequest::AJAX_PARAM ) ) {
            $this->httpResponse->setType( HttpResponse::TYPE_JSON )
                    ->setData( [ 'error' => true, 'message' => $errstr ] )
                    ->send();
        }
        else {
            try {
                throw new \Exception( sprintf( "Meet error on line %s of file %s:\n%s\n", $errline, $errfile, $errstr ) );
            }
            catch ( \Exception $e ) {
                echo sprintf( '<pre>%s</pre>', $this->logError( $e->getMessage() . $e->getTraceAsString() . "\n" ) );
            }
        }
    }

    /**
     * @param string $errno
     * @param string $errstr
     * @param string $errfile
     * @param string $errline
     */
    public function process( $errno, $errstr, $errfile, $errline )
    {
        return $this->area->isCli() ?
                $this->processCliError( $errno, $errstr, $errfile, $errline ) :
                $this->processHttpError( $errno, $errstr, $errfile, $errline );
    }

}
