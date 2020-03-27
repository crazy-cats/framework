<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Module\Model\Source;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     http://crazy-cat.cn
 */
abstract class AbstractSource {

    /**
     * [ label => value ]
     * 
     * @var array
     */
    protected $sourceData = [];

    /**
     * @param boolean $withEmpty
     * @param array|null $array
     * @return array
     */
    public function toOptionArray( $withEmpty = false, $array = null )
    {
        if ( $array === null ) {
            $array = $this->sourceData;
        }
        $options = [];
        foreach ( $array as $label => $value ) {
            if ( is_array( $value ) ) {
                $value = $this->toOptionArray( $withEmpty, $value );
            }
            $options[] = [ 'label' => $label, 'value' => $value ];
        }
        if ( $withEmpty ) {
            array_unshift( $options, [ 'label' => '', 'value' => '' ] );
        }
        return $options;
    }

    /**
     * @param array|null $array
     * @return array
     */
    public function toHashArray( $array = null )
    {
        if ( $array === null ) {
            $array = $this->sourceData;
        }
        foreach ( $array as $label => $value ) {
            if ( is_array( $value ) ) {
                $array = array_merge( $array, $this->toHashArray( $value ) );
            }
            else {
                $array[$value] = $label;
            }
        }
        return $array;
    }

    /**
     * @param string $value
     * @return string|null
     */
    public function getLabel( $value )
    {
        $tmp = $this->toHashArray();
        if ( is_array( $value ) ) {
            foreach ( $value as &$v ) {
                $v = isset( $tmp[$v] ) ? $tmp[$v] : null;
            }
            return implode( ', ', $value );
        }
        else {
            return isset( $tmp[$value] ) ? $tmp[$value] : null;
        }
    }

}
