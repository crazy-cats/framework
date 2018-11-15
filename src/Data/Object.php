<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\Data;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class Object implements \ArrayAccess {

    /**
     * @var array
     */
    protected $data = [];

    public function __construct( array $data = [] )
    {
        $this->data = $data;
    }

    /**
     * @param string|null $key
     * @return mixed
     */
    public function getData( $key = null )
    {
        return ( $key !== null ) ?
                ( isset( $this->data[(string) $key] ) ? $this->data[(string) $key] : null ) :
                $this->data;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function addData( array $data )
    {
        foreach ( $data as $key => $value ) {
            $this->data[$key] = $value;
        }
        return $this;
    }

    /**
     * @param string|array $key|$data
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

    /**
     * @param string $key
     * @return $this
     */
    public function unsetData( $key = null )
    {
        if ( $key === null ) {
            $this->data = [];
        }
        else {
            unset( $this->data[$key] );
        }
        return $this;
    }

    /**
     * @param string|int $offset
     * @return boolean
     */
    public function offsetExists( $offset )
    {
        return isset( $this->data[$offset] );
    }

    /**
     * @param string|int $offset
     * @return mixed
     */
    public function offsetGet( $offset )
    {
        return isset( $this->data[$offset] ) ? $this->data[$offset] : null;
    }

    /**
     * @param string|int $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet( $offset, $value )
    {
        $this->data[$offset] = $value;
    }

    /**
     * @param string|int $offset
     * @return void
     */
    public function offsetUnset( $offset )
    {
        unset( $this->data[$offset] );
    }

    /**
     * @param array|null $data
     * @param array $objectHashs
     * @return array
     */
    public function debug( $data = null, &$objectHashs = [] )
    {
        if ( $data === null ) {
            $hash = spl_object_hash( $this );
            if ( isset( $objectHashs[$hash] ) ) {
                return '--- RECURSION ---';
            }
            $objectHashs[$hash] = true;
            $data = $this->getData();
        }

        $result = [];
        foreach ( $data as $key => $value ) {
            if ( is_scalar( $value ) ) { // numeric, string, boolean etc.
                $result[sprintf( '%s (%s)', $key, gettype( $value ) )] = $value;
            }
            elseif ( $value instanceof Object ) {
                $result[sprintf( '%s (%s)', $key, get_class( $value ) )] = $value->debug( null, $objectHashs );
            }
            elseif ( is_array( $value ) ) {
                $result[$key] = $this->debug( $data, $objectHashs );
            }
        }
        return $result;
    }

    /**
     * @param array $data
     * @return string
     */
    public function toString( array $data, $level = 1 )
    {
        $prefix = str_repeat( ' ', $level * 4 );

        $arrString = [];
        foreach ( $data as $key => $value ) {
            switch ( strtolower( gettype( $value ) ) ) {

                case 'integer' :
                case 'double' :
                    $value = $value;
                    break;

                case 'string' :
                    $value = '\'' . str_replace( '\'', '\\\'', $value ) . '\'';
                    break;

                case 'null' :
                    $value = 'null';
                    break;

                case 'boolean' :
                    $value = $value ? 'true' : 'false';
                    break;

                case 'array' :
                    $value = $this->toString( $value, $level + 1 );
                    break;
            }
            $arrString[] = $prefix . sprintf( '\'%s\' => %s', str_replace( '\'', '\\\'', $key ), $value );
        }

        return sprintf( "[\n%s\n%s]", implode( ",\n", $arrString ), str_repeat( ' ', ( $level - 1 ) * 4 ) );
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toString( $this->getData() );
    }

}
