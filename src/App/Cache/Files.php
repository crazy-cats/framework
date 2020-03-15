<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Cache;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     http://crazy-cat.cn
 */
class Files extends AbstractCache {

    const DIR = DIR_VAR . DS . 'cache';

    /**
     * @return void
     */
    protected function init()
    {
        $file = self::DIR . DS . $this->name;
        $this->data = is_file( $file ) ? json_decode( file_get_contents( $file ), true ) : [];
    }

    /**
     * @return void
     */
    public function save()
    {
        if ( !is_dir( self::DIR ) ) {
            mkdir( self::DIR, 0755, true );
        }
        file_put_contents( self::DIR . DS . $this->name, json_encode( $this->data ) );
    }

    /**
     * @return void
     */
    public function clear()
    {
        if ( is_file( self::DIR . DS . $this->name ) ) {
            unlink( self::DIR . DS . $this->name );
        }
    }

}
