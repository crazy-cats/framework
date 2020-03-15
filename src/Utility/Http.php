<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\Utility;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     http://crazy-cat.cn
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
