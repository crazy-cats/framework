<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Io\Http\Response;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     http://crazy-cat.cn
 */
class ContentType {

    /**
     * @param string $ext
     * @return string
     */
    public function getByExt( $ext )
    {
        switch ( $ext ) {

            case 'js' : return 'application/javascript';
            case 'jsonp' : return 'application/javascript';
            case 'json' : return 'application/json';
            case 'xml' : return 'application/xml';
            case 'css' : return 'text/css';
            case 'csv' : return 'text/csv';

            case 'ico' : return 'image/x-icon';
            case 'gif' : return 'image/gif';
            case 'png' : return 'image/png';
            case 'jpeg' : return 'image/jpeg';
            case 'jpg' : return 'image/jpeg';
            case 'svg' : return 'image/svg+xml';

            case 'eot' : return 'application/vnd.ms-fontobject';
            case 'ttf' : return 'application/x-font-ttf';
            case 'otf' : return 'application/x-font-otf';
            case 'woff' : return 'application/x-font-woff';
            case 'woff2' : return 'application/x-font-woff2';

            case 'gzip' : return 'application/gzip';
            case 'gz' : return 'application/x-gzip';
            case 'bz2' : return 'application/x-bzip2';

            case 'swf' : return 'application/x-shockwave-flash';

            default : return 'text/plain';
        }
    }

}
