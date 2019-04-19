<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\Utility;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class Curl {

    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_DELETE = 'DELETE';

    /**
     * @param string $method
     * @param string $url
     * @param string|array|null $data
     * @param array $headers
     * @return mixed
     */
    static private function request( $method, $url, $data = null, $headers = [] )
    {
        $ch = curl_init();

        switch ( $method ) {

            case self::METHOD_POST :
                curl_setopt( $ch, CURLOPT_POST, 1 );
                curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
                break;

            case self::METHOD_PUT :
                curl_setopt( $ch, CURLOPT_POST, 0 );
                curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'PUT' );
                curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
                break;

            case self::METHOD_DELETE :
                curl_setopt( $ch, CURLOPT_POST, 0 );
                curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'DELETE' );
                curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
                break;

            case self::METHOD_GET :
            default :
                curl_setopt( $ch, CURLOPT_POST, 0 );
                if ( $data !== null && !is_object( $data ) && !is_array( $data ) ) {
                    $data = json_decode( $data, true );
                    if ( !is_array( $data ) ) {
                        $data = null;
                    }
                }
                if ( !empty( $data ) ) {
                    $url .= ( strpos( $url, '?' ) === false ? '?' : '&' ) . http_build_query( $data );
                }
                break;
        }

        curl_setopt( $ch, CURLOPT_HEADER, 0 );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 2 );
        curl_setopt( $ch, CURLOPT_HTTP_VERSION, 1 );
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );

        $response = curl_exec( $ch );
        $error = curl_error( $ch );
        curl_close( $ch );

        if ( $error ) {
            throw new \Exception( $error );
        }

        return $response;
    }

    /**
     * @param string $url
     * @param string|array|null $data
     * @param array $headers
     * @return mixed
     */
    static public function get( $url, $data = null, $headers = [] )
    {
        return self::request( self::METHOD_GET, $url, $data, $headers );
    }

    /**
     * @param string $url
     * @param string|array|null $data
     * @param array $headers
     * @return mixed
     */
    static public function post( $url, $data = null, $headers = [] )
    {
        return self::request( self::METHOD_POST, $url, $data, $headers );
    }

    /**
     * @param string $url
     * @param string|array|null $data
     * @param array $headers
     * @return mixed
     */
    static public function put( $url, $data = null, $headers = [] )
    {
        return self::request( self::METHOD_PUT, $url, $data, $headers );
    }

    /**
     * @param string $url
     * @param string|array|null $data
     * @param array $headers
     * @return mixed
     */
    static public function delete( $url, $data = null, $headers = [] )
    {
        return self::request( self::METHOD_DELETE, $url, $data, $headers );
    }

}
