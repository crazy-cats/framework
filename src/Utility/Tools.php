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
class Tools
{
    /**
     * @param string $string
     * @return string
     */
    public static function strToHump($string)
    {
        return str_replace(
            '_',
            '',
            ucwords(
                trim(str_replace(' ', '_', strtolower(preg_replace('/\W+/', '_', $string))), '_'),
                '_'
            )
        );
    }

    /**
     * @param string $string
     * @return string
     */
    public static function strToSeparated($string)
    {
        return strtolower(
            trim(str_replace(' ', '_', preg_replace('/([A-Z])/', '_$1', preg_replace('/\W+/', '_', $string))), '_')
        );
    }

    /**
     * @param array $optionsArray
     * @return array
     */
    public static function toHashArray(array $optionsArray)
    {
        $hash = [];
        foreach ($optionsArray as $row) {
            $hash[$row['value']] = $row['label'];
        }
        return $hash;
    }

    /**
     * @param array $hashArray
     * @return array
     */
    public static function toOptionsArray(array $hashArray)
    {
        $options = [];
        foreach ($hashArray as $value => $label) {
            $options[] = ['label' => $label, 'value' => $value];
        }
        return $options;
    }
}
