<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class Object {

    protected $data;

    /**
     * @param string $key
     * @return mixed
     */
    public function getData( $key = null )
    {
        return ( $key !== null ) ?
                ( isset( $this->data[(string) $key] ) ? $this->data[(string) $key] : null ) :
                $this->data;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function addData( $key, $value )
    {
        $this->data[(string) $key] = $value;
        return $this;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setData()
    {
        if ( func_num_args() === 1 ) {
            $this->data = (array) func_get_arg( 0 );
        }
        else {
            $this->data[(string) func_get_arg( 0 )] = func_get_arg( 1 );
        }
        return $this;
    }

}
