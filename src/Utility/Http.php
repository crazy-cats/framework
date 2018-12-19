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
class Http {

    static public function getRemoteIp()
    {
        $server = filter_input_array( INPUT_SERVER );

        if ( !empty( $server['HTTP_CLIENT_IP'] ) ) {
            $ip = $server['HTTP_CLIENT_IP'];
        }
        else if ( !empty( $server['HTTP_X_FORWARDED_FOR'] ) ) {
            $ip = $server['HTTP_X_FORWARDED_FOR'];
        }
        else {
            $ip = $server['REMOTE_ADDR'];
        }

        return $ip;
    }

}
