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
 * @link     https://crazy-cat.cn
 */
class Coding
{
    /**
     * @param string $string
     * @return string
     */
    public function strToHump($string)
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
    public function strToSeparated($string)
    {
        return strtolower(
            trim(str_replace(' ', '_', preg_replace('/([A-Z])/', '_$1', preg_replace('/\W+/', '_', $string))), '_')
        );
    }
}
