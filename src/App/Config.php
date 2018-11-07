<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class Config extends \CrazyCat\Framework\Data\Object {

    const DIR = DIR_APP . DS . 'config';

    public function __construct()
    {
        if ( !is_file( self::DIR . DS . 'env.php' ) ) {
            throw new \Exception( 'Config file does not exist.' );
        }
        parent::__construct( require self::DIR . DS . 'env.php' );
    }

    /**
     * @param array $data
     * @return string
     */
    public function toString( array $data )
    {
        $arrString = [];
        foreach ( $data as $key => $value ) {
            switch ( gettype( $value ) ) {

                case 'integer' :
                case 'double' :
                    $value = $value;
                    break;

                case 'string' :
                    $value = str_replace( '\'', '\\\'', $value );
                    break;

                case 'null' :
                    $value = 'null';
                    break;

                case 'boolean' :
                    $value = $value ? 'true' : 'false';
                    break;

                case 'array' :
                    $value = $this->toString( $value );
                    break;
            }
            $arrString[] = sprintf( '\'%s\' => %s', str_replace( '\'', '\\\'', $key ), $value );
        }

        return '[ ' . implode( ', ', $arrString ) . ' ]';
    }

}
