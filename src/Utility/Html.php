<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\Utility;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     http://crazy-cat.cn
 */
class Html {

    /**
     * @return string
     */
    static public function spaceString()
    {
        return html_entity_decode( '&#160;', ENT_NOQUOTES, 'UTF-8' );
    }

    /**
     * @param array $options
     * @param array|string|null $value
     * @return string
     */
    static public function selectOptionsHtml( array $options, $value = null )
    {
        if ( !is_array( $value ) ) {
            $value = [ $value ];
        }
        $html = '';
        foreach ( $options as $option ) {
            if ( is_array( $option['value'] ) ) {
                $html .= sprintf( '<optgroup label="%s">%s</optgroup>', htmlEscape( $option['label'] ), self::selectOptionsHtml( $option['value'], $value ) );
            }
            else {
                $html .= sprintf( '<option value="%s"%s>%s</option>', htmlEscape( $option['value'] ), ( in_array( $option['value'], $value, true ) ? ' selected="selected"' : '' ), htmlEscape( $option['label'] ) );
            }
        }
        return $html;
    }

}
